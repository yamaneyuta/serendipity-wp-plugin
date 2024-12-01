<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\Update\Version;

use Cornix\Serendipity\Core\Lib\Database\Schema\InvoiceNonceTable;
use Cornix\Serendipity\Core\Lib\Database\Schema\InvoiceTable;
use Cornix\Serendipity\Core\Lib\Database\Schema\TokenTable;
use Cornix\Serendipity\Core\Lib\Database\Schema\UnlockPaywallTransactionTable;
use Cornix\Serendipity\Core\Lib\Database\Schema\UnlockPaywallTransferEventTable;
use Cornix\Serendipity\Core\Lib\Repository\Constants\ChainID;
use Cornix\Serendipity\Core\Lib\Repository\Environment;
use Cornix\Serendipity\Core\Lib\Repository\PayableTokens;
use Cornix\Serendipity\Core\Lib\Repository\ServerSignerData;
use Cornix\Serendipity\Core\Lib\Web3\Ethers;
use Cornix\Serendipity\Core\Lib\Web3\PrivateKey;
use Cornix\Serendipity\Core\Types\Token;

/**
 * Ver0.0.1(インストール直後に実行されるように一番小さいバージョンで仮作成)
 */
class v001 {

	public function up() {
		// 署名用ウォレットの秘密鍵を初期化
		( new PrivateKeyInitializer() )->initialize();

		// 購入者が支払可能なトークンの初期値を設定
		( new PayableTokensInitializer() )->initialize();

		global $wpdb;
		// 請求書情報テーブルを作成
		( new InvoiceTable( $wpdb ) )->create();
		// 請求書とnonceの紐づきを保存するテーブルを作成
		( new InvoiceNonceTable( $wpdb ) )->create();
		// トークンの情報を記録するテーブルを作成
		( new TokenTable( $wpdb ) )->create();
		// ペイウォール解除時のトランザクションに関するデータを記録するテーブルを作成
		( new UnlockPaywallTransactionTable( $wpdb ) )->create();
		// ペイウォール解除時のトークン転送イベントの内容を記録するテーブルを作成
		( new UnlockPaywallTransferEventTable( $wpdb ) )->create();
	}

	public function down() {
		// 署名用ウォレットの秘密鍵の削除は行わない

		global $wpdb;
		// 請求書情報テーブルを削除
		( new InvoiceTable( $wpdb ) )->drop();
		// 請求書とnonceの紐づきを保存するテーブルを削除
		( new InvoiceNonceTable( $wpdb ) )->drop();
		// トークンの情報を記録するテーブルを削除
		( new TokenTable( $wpdb ) )->drop();
		// ペイウォール解除時のトランザクションに関するデータを記録するテーブルを削除
		( new UnlockPaywallTransactionTable( $wpdb ) )->drop();
		// ペイウォール解除時のトークン転送イベントの内容を記録するテーブルを削除
		( new UnlockPaywallTransferEventTable( $wpdb ) )->drop();
	}
}


class PayableTokensInitializer {
	/**
	 * 購入者が支払可能なトークンの初期値を設定します。
	 */
	public function initialize(): void {
		$this->initMainnet();
		$this->initTestnet();
		$this->initPrivatenet();
	}

	private function initMainnet(): void {
		// メインネットの場合はEthereum mainnetのみ
		$eth = Token::from( ChainID::ETH_MAINNET, Ethers::zeroAddress() );
		( new PayableTokens() )->save( $eth->chainID(), array( $eth ) );
	}

	private function initTestnet(): void {
		// テストネットの場合はSepoliaのみ
		$eth = Token::from( ChainID::SEPOLIA, Ethers::zeroAddress() );
		( new PayableTokens() )->save( $eth->chainID(), array( $eth ) );
	}

	private function initPrivatenet(): void {
		// 開発モードの時のみ、プライベートネットの設定を追加
		if ( ( new Environment() )->isDevelopmentMode() ) {

			// Privatenet L1
			{
				$eth = Token::from( ChainID::PRIVATENET_L1, Ethers::zeroAddress() );
				( new PayableTokens() )->save( $eth->chainID(), array( $eth ) );
			}

			// Privatenet L2
			{
				$matic = Token::from( ChainID::PRIVATENET_L2, Ethers::zeroAddress() );
				( new PayableTokens() )->save( $matic->chainID(), array( $matic ) );
			}
		}
	}
}


class PrivateKeyInitializer {
	/**
	 * 署名用ウォレットの秘密鍵が存在しない場合は生成して保存します。
	 */
	public function initialize(): void {
		$server_signer_data = new ServerSignerData();

		if ( ! $server_signer_data->exists() ) {
			// 秘密鍵を生成して保存
			$private_key = ( new PrivateKey() )->generate();
			$server_signer_data->save( $private_key );
		}
	}
}
