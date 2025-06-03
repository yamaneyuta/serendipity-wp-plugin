<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Entity;

use Cornix\Serendipity\Core\ValueObject\NetworkCategory;
use Cornix\Serendipity\Core\ValueObject\Price;
use Cornix\Serendipity\Core\ValueObject\TableRecord\PaidContentTableRecord;

class Post {

	public function __construct( int $post_id, ?string $paid_content, ?NetworkCategory $selling_network_category, ?Price $selling_price ) {
		$this->post_id                  = $post_id;
		$this->paid_content             = $paid_content;
		$this->selling_network_category = $selling_network_category;
		$this->selling_price            = $selling_price;
	}

	private int $post_id;
	private ?string $paid_content;
	private ?Price $selling_price;
	private ?NetworkCategory $selling_network_category;

	public function id(): int {
		return $this->post_id;
	}
	public function paidContent(): ?string {
		return $this->paid_content;
	}
	public function sellingNetworkCategory(): ?NetworkCategory {
		return $this->selling_network_category;
	}
	public function sellingPrice(): ?Price {
		return $this->selling_price;
	}

	public static function fromTableRecord( PaidContentTableRecord $record ): self {
		return new self(
			$record->postID(),
			$record->paidContent(),
			NetworkCategory::from( $record->sellingNetworkCategoryID() ),
			new Price(
				$record->sellingAmountHex(),
				$record->sellingDecimals(),
				$record->sellingSymbol()
			)
		);
	}
}
