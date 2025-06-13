<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\Entity;

use Cornix\Serendipity\Core\Domain\Entity\PaidContent;
use Cornix\Serendipity\Core\Domain\Entity\Post;
use Cornix\Serendipity\Core\Infrastructure\Database\ValueObject\PaidContentTableRecord;
use Cornix\Serendipity\Core\ValueObject\NetworkCategory;
use Cornix\Serendipity\Core\ValueObject\Price;

class PostImpl extends Post {

	private function __construct( PaidContentTableRecord $record ) {
		parent::__construct(
			$record->postID(),
			PaidContent::from( $record->paidContent() ),
			NetworkCategory::from( $record->sellingNetworkCategoryID() ),
			$this->getPriceFromRecord( $record ),
		);
	}

	private function getPriceFromRecord( PaidContentTableRecord $record ): ?Price {
		$selling_amount_hex = $record->sellingAmountHex();
		$selling_decimals   = $record->sellingDecimals();
		$selling_symbol     = $record->sellingSymbol();
		if ( null === $selling_amount_hex || null === $selling_decimals || null === $selling_symbol ) {
			return null;
		} else {
			return new Price( $selling_amount_hex, $selling_decimals, $selling_symbol );
		}
	}

	public static function fromTableRecord( PaidContentTableRecord $record ): self {
		return new self( $record );
	}
}
