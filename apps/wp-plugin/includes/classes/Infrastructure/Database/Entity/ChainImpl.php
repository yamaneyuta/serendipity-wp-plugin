<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\Entity;

use Cornix\Serendipity\Core\Domain\Entity\Chain;
use Cornix\Serendipity\Core\Infrastructure\Database\ValueObject\ChainTableRecord;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;
use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategoryID;
use Cornix\Serendipity\Core\Domain\ValueObject\Confirmations;
use Cornix\Serendipity\Core\Domain\ValueObject\RpcUrl;

class ChainImpl extends Chain {

	private function __construct( ChainTableRecord $record ) {
		parent::__construct(
			new ChainID( $record->chainIdValue() ),
			$record->nameValue(),
			new NetworkCategoryID( $record->networkCategoryIdValue() ),
			RpcUrl::from( $record->rpcUrlValue() ),
			Confirmations::from( $record->confirmationsValue() ),
			$record->blockExplorerUrlValue()
		);
	}

	public static function fromTableRecord( ChainTableRecord $record ): self {
		return new self( $record );
	}
}
