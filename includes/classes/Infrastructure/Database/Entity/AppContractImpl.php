<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\Entity;

use Cornix\Serendipity\Core\Domain\Entity\AppContract;
use Cornix\Serendipity\Core\Domain\Entity\Chain;
use Cornix\Serendipity\Core\Infrastructure\Database\ValueObject\AppContractTableRecord;
use Cornix\Serendipity\Core\ValueObject\Address;
use Cornix\Serendipity\Core\ValueObject\BlockNumber;

class AppContractImpl extends AppContract {

	private function __construct(
		Chain $chain,
		Address $address,
		BlockNumber $activation_block_number,
		BlockNumber $crawled_block_number
	) {
		parent::__construct( $chain, $address, $activation_block_number, $crawled_block_number );
	}

	public static function fromTableRecord( Chain $chain, AppContractTableRecord $record ): self {
		return new self(
			$chain,
			Address::from( $record->addressValue() ),
			BlockNumber::from( $record->activationBlockNumberValue() ),
			BlockNumber::from( $record->crawledBlockNumberValue() )
		);
	}
}
