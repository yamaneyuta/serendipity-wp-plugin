<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\Update\Version;

use Cornix\Serendipity\Core\Lib\Repository\SignerPrivateKey;
use Cornix\Serendipity\Core\Lib\Web3\PrivateKey;

/**
 * Ver0.0.1(インストール直後に実行されるように一番小さいバージョンで仮作成)
 */
class v001 {

	public function up() {
		// 署名用ウォレットの秘密鍵が存在しない場合は生成して保存
		( new PrivateKeyInitializer() )->initialize();
	}

	public function down() {
		// 署名用ウォレットの秘密鍵の削除は行わない
	}
}


class PrivateKeyInitializer {
	/**
	 * 署名用ウォレットの秘密鍵が存在しない場合は生成して保存します。
	 */
	public function initialize(): void {
		$private_key = ( new SignerPrivateKey() )->get();
		if ( null === $private_key ) {
			$private_key = ( new PrivateKey() )->generate();
			( new SignerPrivateKey() )->save( $private_key );
		}
	}
}
