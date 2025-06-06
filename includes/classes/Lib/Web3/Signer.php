<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Web3;

use Cornix\Serendipity\Core\ValueObject\Address;
use Cornix\Serendipity\Core\ValueObject\PrivateKey;

class Signer {
	/**
	 * @param PrivateKey|string $private_key
	 * @disregard P1009 Undefined type
	 */
	public function __construct(
		#[\SensitiveParameter]
		$private_key
	) {
		$this->private_key = is_string( $private_key ) ? PrivateKey::from( $private_key ) : $private_key;
	}

	private PrivateKey $private_key;

	/**
	 * ウォレットアドレスを取得します。
	 */
	public function address(): Address {
		return Ethers::privateKeyToAddress( $this->private_key );
	}

	/**
	 * メッセージに署名を行います。
	 *
	 * @see https://ethereum.stackexchange.com/a/86503
	 */
	public function signMessage( string $message ): string {
		return Ethers::signMessage( $this->private_key, $message );
	}
}
