<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\Entity;

use Cornix\Serendipity\Core\Domain\Entity\PaidContent;
use Cornix\Serendipity\Core\Domain\Entity\Post;
use Cornix\Serendipity\Core\Infrastructure\Database\ValueObject\PaidContentTableRecord;
use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategoryID;
use Cornix\Serendipity\Core\Domain\ValueObject\PostId;
use Cornix\Serendipity\Core\Domain\ValueObject\Price;

class PostImpl extends Post {

	private function __construct( PaidContentTableRecord $record ) {
		parent::__construct(
			new PostId( $record->postIdValue() ),
			PaidContent::from( $record->paidContentValue() ),
			NetworkCategoryID::from( $record->sellingNetworkCategoryIdValue() ),
			$this->getPriceFromRecord( $record ),
		);
	}

	private function getPriceFromRecord( PaidContentTableRecord $record ): ?Price {
		$selling_amount_hex = $record->sellingAmountHexValue();
		$selling_decimals   = $record->sellingDecimalsValue();
		$selling_symbol     = $record->sellingSymbolValue();
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
