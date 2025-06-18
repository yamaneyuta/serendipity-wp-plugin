<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Service;

use Cornix\Serendipity\Core\Domain\Entity\Signer;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\Signature;
use Cornix\Serendipity\Core\Domain\ValueObject\SigningMessage;

interface WalletService {
	/** 署名を行います。 */
	public function signMessage( Signer $signer, SigningMessage $message ): Signature;

	/** 署名からアドレスを復元します。 */
	public function recoverAddress( SigningMessage $message, Signature $signature ): Address;
}
