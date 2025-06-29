<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3\Service;

use Cornix\Serendipity\Core\Domain\Entity\Signer;
use Cornix\Serendipity\Core\Domain\Service\WalletService;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\Signature;
use Cornix\Serendipity\Core\Domain\ValueObject\SigningMessage;
use Cornix\Serendipity\Core\Infrastructure\Web3\Ethers;

class WalletServiceImpl implements WalletService {
	/** @inheritdoc */
	public function signMessage( Signer $signer, SigningMessage $message ): Signature {
		return Ethers::signMessage( $signer->privateKey(), $message );
	}

	/** @inheritdoc */
	public function recoverAddress( SigningMessage $message, Signature $signature ): Address {
		return Ethers::verifyMessage( $message, $signature );
	}
}
