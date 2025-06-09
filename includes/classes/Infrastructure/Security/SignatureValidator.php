<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Security;

use Cornix\Serendipity\Core\Lib\Web3\Ethers;
use Cornix\Serendipity\Core\ValueObject\Address;

/**
 * 署名を検証するクラス
 */
class SignatureValidator {
	/**
	 * 署名を検証し、アドレスが一致しない場合は例外をスローします。
	 */
	public function checkSignature( string $message, string $signature, Address $signer_address ): void {
		$verified_address = Ethers::verifyMessage( $message, $signature );
		if ( ! $verified_address->equals( $signer_address ) ) {
			throw new \InvalidArgumentException(
				sprintf(
					'[02704C9B] Signature verification failed. Expected address: %s, but got: %s',
					$signer_address->value(),
					$verified_address->value()
				)
			);
		}
	}
}
