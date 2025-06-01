<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Web3;

use Cornix\Serendipity\Core\ValueObject\Address;
use Elliptic\EC;
use Elliptic\EC\KeyPair;
use kornrunner\Keccak;

class Signer {
	/** @disregard P1009 Undefined type */
	public function __construct(
		#[\SensitiveParameter]
		string $private_key
	) {
		$ec             = new EC( 'secp256k1' );
		$this->key_pair = $ec->keyFromPrivate( $private_key );
	}

	private KeyPair $key_pair;

	/**
	 * ウォレットアドレスを取得します。
	 */
	public function address(): Address {
		return Ethers::computeAddress( $this->key_pair->getPublic() );
	}

	/**
	 * メッセージに署名を行います。
	 *
	 * @see https://ethereum.stackexchange.com/a/86503
	 */
	public function signMessage( string $message ): string {
		$message_hash = Keccak::hash( Ethers::eip191( $message ), 256 );

		$signature = $this->key_pair->sign( $message_hash, array( 'canonical' => true ) );

		$r = str_pad( $signature->r->toString( 16 ), 64, '0', STR_PAD_LEFT );
		$s = str_pad( $signature->s->toString( 16 ), 64, '0', STR_PAD_LEFT );
		$v = dechex( $signature->recoveryParam + 27 );

		$signature = "0x$r$s$v";

		return $signature;
	}
}
