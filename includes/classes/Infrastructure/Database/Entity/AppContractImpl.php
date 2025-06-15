<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\Entity;

use Cornix\Serendipity\Core\Domain\Entity\AppContract;
use Cornix\Serendipity\Core\Domain\Entity\Chain;
use Cornix\Serendipity\Core\Infrastructure\Database\ValueObject\AppContractTableRecord;
use Cornix\Serendipity\Core\ValueObject\Address;
use Cornix\Serendipity\Core\ValueObject\BlockNumber;

class AppContractImpl extends AppContract {

	private function __construct( Chain $chain, AppContractTableRecord $record ) {
		parent::__construct(
			$chain,
			Address::from( $record->addressValue() ),
			BlockNumber::from( $record->activationBlockNumberValue() ),
			BlockNumber::from( $record->crawledBlockNumberValue() )
		);
	}

	public static function fromTableRecord( Chain $chain, AppContractTableRecord $record ): self {
			return new self( $chain, $record );
	}
}
