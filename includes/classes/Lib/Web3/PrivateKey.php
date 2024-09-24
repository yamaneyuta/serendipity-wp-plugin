<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Web3;

use Elliptic\EC;

class PrivateKey {
	/**
	 * ウォレットの秘密鍵を新しく生成します。
	 * ※秘密鍵の長さは64文字固定でないことに注意
	 */
	public function generate(): string {
		$ec = new EC( 'secp256k1' );
		return $ec->genKeyPair()->getPrivate( 'hex' );
	}
}
