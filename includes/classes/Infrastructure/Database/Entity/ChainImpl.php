<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\Entity;

use Cornix\Serendipity\Core\Domain\Entity\Chain;
use Cornix\Serendipity\Core\Infrastructure\Database\ValueObject\ChainTableRecord;
use Cornix\Serendipity\Core\ValueObject\ChainID;

class ChainImpl extends Chain {

	private function __construct( ChainTableRecord $record ) {
		/** @var string $confirmations */
		$confirmations = $record->confirmations();
		parent::__construct(
			new ChainID( $record->chainID() ),
			$record->name(),
			$record->rpcURL(),
			is_numeric( $confirmations ) ? (int) $confirmations : $confirmations,
		);
	}

	public static function fromTableRecord( ChainTableRecord $record ): self {
		return new self( $record );
	}
}
