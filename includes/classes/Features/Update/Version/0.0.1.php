<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\Update\Version;

use Cornix\Serendipity\Core\Lib\Database\Schema\PurchaseTicketTable;
use Cornix\Serendipity\Core\Lib\Repository\Constants\ChainID;
use Cornix\Serendipity\Core\Lib\Repository\Environment;
use Cornix\Serendipity\Core\Lib\Repository\PayableChainIDs;
use Cornix\Serendipity\Core\Lib\Repository\SignerPrivateKey;
use Cornix\Serendipity\Core\Lib\Web3\PrivateKey;
use Cornix\Serendipity\Core\Types\NetworkCategory;

/**
 * Ver0.0.1(インストール直後に実行されるように一番小さいバージョンで仮作成)
 */
class v001 {

	public function up() {
		// 署名用ウォレットの秘密鍵を初期化
		( new PrivateKeyInitializer() )->initialize();

		// 購入者が支払可能なチェーンIDの初期値を設定
		( new PayableChainIDsInitializer() )->initialize();

		global $wpdb;
		// 購入用チケットテーブルを作成
		( new PurchaseTicketTable( $wpdb ) )->create();
	}

	public function down() {
		// 署名用ウォレットの秘密鍵の削除は行わない

		global $wpdb;
		// 購入用チケットテーブルを削除
		( new PurchaseTicketTable( $wpdb ) )->drop();
	}
}


class PayableChainIDsInitializer {
	/**
	 * 購入者が支払可能なチェーンIDの初期値を設定します。
	 */
	public function initialize(): void {
		$this->initMainnet();
		$this->initTestnet();
		$this->initPrivatenet();
	}

	private function initMainnet(): void {
		// メインネットの場合はEthereumのみ
		$chain_ids = array( ChainID::ETH_MAINNET );
		( new PayableChainIDs() )->save( NetworkCategory::mainnet(), $chain_ids );
	}

	private function initTestnet(): void {
		// テストネットの場合はSepoliaのみ
		$chain_ids = array( ChainID::SEPOLIA );
		( new PayableChainIDs() )->save( NetworkCategory::testnet(), $chain_ids );
	}

	private function initPrivatenet(): void {
		// 開発モードの時のみ、プライベートネットの設定を追加
		if ( ( new Environment() )->isDevelopmentMode() ) {
			// プライベートネットの場合はL1とL2を模したチェーンIDを設定
			$chain_ids = array( ChainID::PRIVATENET_L1, ChainID::PRIVATENET_L2 );
			( new PayableChainIDs() )->save( NetworkCategory::privatenet(), $chain_ids );
		}
	}
}


class PrivateKeyInitializer {
	/**
	 * 署名用ウォレットの秘密鍵が存在しない場合は生成して保存します。
	 */
	public function initialize(): void {
		$signer_private_key = new SignerPrivateKey();

		if ( ! $signer_private_key->exists() ) {
			// 秘密鍵を生成して保存
			$private_key = ( new PrivateKey() )->generate();
			$signer_private_key->save( $private_key );
		}
	}
}
