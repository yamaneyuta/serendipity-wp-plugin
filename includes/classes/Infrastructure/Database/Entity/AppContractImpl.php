<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\Entity;

use Cornix\Serendipity\Core\Domain\Entity\AppContract;
use Cornix\Serendipity\Core\Domain\Entity\Chain;
use Cornix\Serendipity\Core\Infrastructure\Database\ValueObject\AppContractTableRecord;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\Infrastructure\Format\UnixTimestampFormat;

class AppContractImpl extends AppContract {

	private function __construct( Chain $chain, AppContractTableRecord $record ) {
		parent::__construct(
			$chain,
			Address::from( $record->addressValue() ),
			BlockNumber::from( $record->crawledBlockNumberValue() ),
			UnixTimestampFormat::fromMySQL( $record->crawledBlockNumberUpdatedAtValue() )
		);
	}

	public static function fromTableRecord( Chain $chain, AppContractTableRecord $record ): self {
			return new self( $chain, $record );
	}
}
