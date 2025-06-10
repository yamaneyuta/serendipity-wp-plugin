<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\Update\Version;

use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\ChainTable;
use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\InvoiceTable;
use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\OracleTable;
use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\PaidContentTable;
use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\TokenTable;
use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\UnlockPaywallTransactionTable;
use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\UnlockPaywallTransferEventTable;
use Cornix\Serendipity\Core\Constant\ChainID;
use Cornix\Serendipity\Core\Repository\Environment;
use Cornix\Serendipity\Core\Lib\Web3\Ethers;
use Cornix\Serendipity\Core\Entity\Token;
use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\ServerSignerTable;
use Cornix\Serendipity\Core\Service\Factory\ServerSignerServiceFactory;

/**
 * Ver0.0.1(インストール直後に実行されるように一番小さいバージョンで仮作成)
 */
class v001 {

	public function up() {
		global $wpdb;
		// チェーン情報を管理するためのテーブルを作成
		( new ChainTable( $wpdb ) )->create();
		// 署名用ウォレットの秘密鍵を保存するテーブルを作成
		( new ServerSignerTable( $wpdb ) )->create();
		// 有料記事を管理するためのテーブルを作成
		( new PaidContentTable( $wpdb ) )->create();
		// 請求書情報テーブルを作成
		( new InvoiceTable( $wpdb ) )->create();
		// Oracleの情報を記録するテーブルを作成
		( new OracleTable( $wpdb ) )->create();
		// トークンの情報を記録するテーブルを作成
		( new TokenTable( $wpdb ) )->create();
		// ペイウォール解除時のトランザクションに関するデータを記録するテーブルを作成
		( new UnlockPaywallTransactionTable( $wpdb ) )->create();
		// ペイウォール解除時のトークン転送イベントの内容を記録するテーブルを作成
		( new UnlockPaywallTransferEventTable( $wpdb ) )->create();

		( new ServerSignerServiceFactory() )->create( $wpdb )->initializeServerSigner();
		// チェーンテーブルの初期値を設定
		( new ChainTableRecordInitializer( $wpdb ) )->initialize();
		// oracleテーブルの初期値を設定
		( new OracleTableRecordInitializer( $wpdb ) )->initialize();
		// tokenテーブルの初期値を設定
		( new TokenTableRecordInitializer( $wpdb ) )->initialize();
	}

	public function down() {
		// 署名用ウォレットの秘密鍵の削除は行わない

		global $wpdb;
		// チェーン情報を管理するためのテーブルを削除
		( new ChainTable( $wpdb ) )->drop();
		// 署名用ウォレットの秘密鍵を保存するテーブルを削除
		( new ServerSignerTable( $wpdb ) )->drop();
		// 有料記事を管理するためのテーブルを削除
		( new PaidContentTable( $wpdb ) )->drop();
		// 請求書情報テーブルを削除
		( new InvoiceTable( $wpdb ) )->drop();
		// Oracleの情報を記録するテーブルを削除
		( new OracleTable( $wpdb ) )->drop();
		// トークンの情報を記録するテーブルを削除
		( new TokenTable( $wpdb ) )->drop();
		// ペイウォール解除時のトランザクションに関するデータを記録するテーブルを削除
		( new UnlockPaywallTransactionTable( $wpdb ) )->drop();
		// ペイウォール解除時のトークン転送イベントの内容を記録するテーブルを削除
		( new UnlockPaywallTransferEventTable( $wpdb ) )->drop();
	}
}


class ChainTableRecordInitializer {
	private $wpdb;

	public function __construct( $wpdb ) {
		$this->wpdb = $wpdb;
	}

	/**
	 * チェーンテーブルの初期値を設定します。
	 */
	public function initialize(): void {
		$chain_table = new ChainTable( $this->wpdb );

		// メインネットのチェーン情報を登録
		$chain_table->insert( ChainID::ETH_MAINNET, 'Ethereum Mainnet' );

		// テストネットのチェーン情報を登録
		$chain_table->insert( ChainID::SEPOLIA, 'Sepolia' );
		$chain_table->insert( ChainID::SONEIUM_MINATO, 'Soneium Testnet Minato' );

		// 開発モード時はプライベートネットのチェーン情報も登録
		if ( ( new Environment() )->isDevelopmentMode() ) {
			$chain_table->insert( ChainID::PRIVATENET_L1, 'Privatenet1' );
			$chain_table->insert( ChainID::PRIVATENET_L2, 'Privatenet2' );

			// プライベートネットのRPC URLを設定
			$chain_table->updateRpcURL( ChainID::PRIVATENET_L1, $this->getPrivatenetRpcURL( ChainID::PRIVATENET_L1 ) );
			$chain_table->updateRpcURL( ChainID::PRIVATENET_L2, $this->getPrivatenetRpcURL( ChainID::PRIVATENET_L2 ) );
		}
	}


	/**
	 * 指定されたチェーンIDに対応するプライベートネットのRPC URLを取得します。
	 *
	 * @param int $chain_ID
	 */
	private function getPrivatenetRpcURL( int $chain_ID ): ?string {

		// プライベートネットのURLを取得する関数
		$privatenet = function ( int $number ): string {
			assert( in_array( $number, array( 1, 2 ), true ) );
			$prefix = ( new Environment() )->isTesting() ? 'tests-' : '';
			return "http://{$prefix}privatenet-{$number}.local";
		};

		switch ( $chain_ID ) {
			case ChainID::PRIVATENET_L1:
				return $privatenet( 1 );
			case ChainID::PRIVATENET_L2:
				return $privatenet( 2 );
			default:
				throw new \InvalidArgumentException( '[AC32E587] Invalid chain ID. ' . $chain_ID );
		}
	}
}


class OracleTableRecordInitializer {
	private $wpdb;

	public function __construct( $wpdb ) {
		$this->wpdb = $wpdb;
	}

	/**
	 * Oracleテーブルの初期値を設定します。
	 */
	public function initialize(): void {
		$oracle_table = new OracleTable( $this->wpdb );

		// メインネットのOracleを登録
		// Fiatはメインネットに存在するものをすべて登録(2025/1/30時点)、CryptoはETH/USDのみ(動作する最低限)を登録
		// ※ 最新の情報をWEBから取得して登録することはしない(WEBサイトが停止された場合に1つも登録できなくなる、等のリスクがあるため)

		// Fiat
		// $oracle_table->insert( ChainID::ETH_MAINNET, '0x01435677FB11763550905594A16B645847C1d0F3', 'KRW', 'USD' );
		// $oracle_table->insert( ChainID::ETH_MAINNET, '0x77F9710E7d0A19669A13c055F62cd80d313dF022', 'AUD', 'USD' );
		// $oracle_table->insert( ChainID::ETH_MAINNET, '0x5c0Ab2d9b5a7ed9f470386e82BB36A3613cDd4b5', 'GBP', 'USD' );
		// $oracle_table->insert( ChainID::ETH_MAINNET, '0x449d117117838fFA61263B61dA6301AA2a88B13A', 'CHF', 'USD' );
		// $oracle_table->insert( ChainID::ETH_MAINNET, '0xeF8A4aF35cd47424672E3C590aBD37FBB7A7759a', 'CNY', 'USD' );
		// $oracle_table->insert( ChainID::ETH_MAINNET, '0xb49f677943BC038e9857d61E7d053CaA2C1734C1', 'EUR', 'USD' );
		$oracle_table->insert( ChainID::ETH_MAINNET, '0xBcE206caE7f0ec07b545EddE332A47C2F75bbeb3', 'JPY', 'USD' );
		// $oracle_table->insert( ChainID::ETH_MAINNET, '0xB09fC5fD3f11Cf9eb5E1C5Dba43114e3C9f477b5', 'TRY', 'USD' );
		// $oracle_table->insert( ChainID::ETH_MAINNET, '0xa34317DB73e77d453b1B8d04550c44D10e981C8e', 'CAD', 'USD' );
		// $oracle_table->insert( ChainID::ETH_MAINNET, '0x3977CFc9e4f29C184D4675f4EB8e0013236e5f3e', 'NZD', 'USD' );
		// $oracle_table->insert( ChainID::ETH_MAINNET, '0xe25277fF4bbF9081C75Ab0EB13B4A13a721f3E13', 'SGD', 'USD' );
		// Crypto
		$oracle_table->insert( ChainID::ETH_MAINNET, '0x5f4eC3Df9cbd43714FE2740f5E3616155c5b8419', 'ETH', 'USD' );

		// テスト中はプライベートネットのOracleを登録
		if ( ( new Environment() )->isTesting() ) {
			// プライベートネットのOracleを登録
			$oracle_table->insert( ChainID::PRIVATENET_L1, '0x3F3B6a555F3a7DeD78241C787e0cDD8E431A64A8', 'ETH', 'USD' );
			$oracle_table->insert( ChainID::PRIVATENET_L1, '0xc886d2C1BEC5819b4B8F84f35A9885519869A8EE', 'JPY', 'USD' );
		}
	}
}


class TokenTableRecordInitializer {
	private $wpdb;

	public function __construct( $wpdb ) {
		$this->wpdb = $wpdb;
	}

	/**
	 * トークンテーブルの初期値を設定します。
	 */
	public function initialize(): void {
		$token_table = new TokenTable( $this->wpdb );

		// メインネットのネイティブトークンを登録(Ethereum Mainnetのみ支払可能として指定)
		$token_table->save( Token::from( ChainID::ETH_MAINNET, Ethers::zeroAddress(), 'ETH', 18, true ) );

		// テストネットのネイティブトークンを登録(Sepoliaのみ支払可能として指定)
		$token_table->save( Token::from( ChainID::SEPOLIA, Ethers::zeroAddress(), 'ETH', 18, true ) );
		$token_table->save( TOken::from( ChainID::SONEIUM_MINATO, Ethers::zeroAddress(), 'ETH', 18, false ) );

		// 開発モード時はプライベートネットのネイティブトークンを登録
		if ( ( new Environment() )->isDevelopmentMode() ) {
			$token_table->save( Token::from( ChainID::PRIVATENET_L1, Ethers::zeroAddress(), 'ETH', 18, true ) );
			$token_table->save( Token::from( ChainID::PRIVATENET_L2, Ethers::zeroAddress(), 'MATIC', 18, true ) );
		}
	}
}
