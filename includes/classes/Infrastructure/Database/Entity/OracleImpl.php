<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\Entity;

use Cornix\Serendipity\Core\Domain\Entity\Oracle;
use Cornix\Serendipity\Core\Infrastructure\Database\ValueObject\ChainTableRecord;
use Cornix\Serendipity\Core\Infrastructure\Database\ValueObject\OracleTableRecord;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;

class OracleImpl extends Oracle {

	private function __construct( OracleTableRecord $oracle_record, ChainTableRecord $chain_record ) {
		parent::__construct(
			ChainImpl::fromTableRecord( $chain_record ),
			new Address( $oracle_record->addressValue() ),
			$oracle_record->baseSymbolValue(),
			$oracle_record->quoteSymbolValue()
		);
	}

	public static function fromTableRecord( OracleTableRecord $oracle_record, ChainTableRecord $chain_record ): self {
		return new self( $oracle_record, $chain_record );
	}
}
