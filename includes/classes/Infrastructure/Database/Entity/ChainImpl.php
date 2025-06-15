<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\Entity;

use Cornix\Serendipity\Core\Domain\Entity\Chain;
use Cornix\Serendipity\Core\Infrastructure\Database\ValueObject\ChainTableRecord;
use Cornix\Serendipity\Core\ValueObject\ChainID;

class ChainImpl extends Chain {

	private function __construct( ChainTableRecord $record ) {
		$confirmations_value = $record->confirmationsValue();
		parent::__construct(
			new ChainID( $record->chainIdValue() ),
			$record->nameValue(),
			$record->rpcUrlValue(),
			is_numeric( $confirmations_value ) ? (int) $confirmations_value : $confirmations_value,
		);
	}

	public static function fromTableRecord( ChainTableRecord $record ): self {
		return new self( $record );
	}
}
