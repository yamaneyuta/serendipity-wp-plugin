<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\Update\Version;

use Cornix\Serendipity\Core\Lib\Database\Schema\PricePatternTable;
use Cornix\Serendipity\Core\Lib\Repository\SignerPrivateKey;
use Cornix\Serendipity\Core\Lib\Web3\PrivateKey;

/**
 * Ver0.0.1(インストール直後に実行されるように一番小さいバージョンで仮作成)
 */
class v001 {

	public function up() {
		// 署名用ウォレットの秘密鍵を初期化
		( new PrivateKeyInitializer() )->initialize();

		global $wpdb;
		// 価格パターンテーブルを作成
		( new PricePatternTable( $wpdb ) )->create();
	}

	public function down() {
		// 署名用ウォレットの秘密鍵の削除は行わない

		global $wpdb;
		// 価格パターンテーブルを削除
		( new PricePatternTable( $wpdb ) )->drop();
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
