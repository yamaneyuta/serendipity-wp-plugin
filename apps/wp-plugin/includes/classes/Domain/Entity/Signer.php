<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Entity;

use Cornix\Serendipity\Core\Infrastructure\Web3\Ethers;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\PrivateKey;
use Cornix\Serendipity\Core\Domain\ValueObject\Signature;
use Cornix\Serendipity\Core\Domain\ValueObject\SigningMessage;

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

	public function privateKey(): PrivateKey {
		return $this->private_key;
	}

	/**
	 * ウォレットアドレスを取得します。
	 */
	public function address(): Address {
		return Ethers::privateKeyToAddress( $this->private_key );
	}

	/**
	 * メッセージに署名を行います。
	 *
	 * @deprecated Use WalletService::signMessage instead
	 */
	public function signMessage( SigningMessage $message ): Signature {
		return Ethers::signMessage( $this->private_key, $message );
	}
}
