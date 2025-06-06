<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Entity;

use Cornix\Serendipity\Core\ValueObject\NetworkCategory;
use Cornix\Serendipity\Core\ValueObject\Price;
use Cornix\Serendipity\Core\Infrastructure\Database\ValueObject\PaidContentTableRecord;

class Post {

	public function __construct( int $post_id, ?PaidContent $paid_content, ?NetworkCategory $selling_network_category, ?Price $selling_price ) {
		$this->post_id                  = $post_id;
		$this->paid_content             = $paid_content;
		$this->selling_network_category = $selling_network_category;
		$this->selling_price            = $selling_price;
	}

	private int $post_id;
	private ?PaidContent $paid_content;
	private ?Price $selling_price;
	private ?NetworkCategory $selling_network_category;

	public function id(): int {
		return $this->post_id;
	}
	public function paidContent(): ?PaidContent {
		return $this->paid_content;
	}
	public function setPaidContent( ?PaidContent $paid_content ): void {
		$this->paid_content = $paid_content;
	}
	public function sellingNetworkCategory(): ?NetworkCategory {
		return $this->selling_network_category;
	}
	public function setSellingNetworkCategory( ?NetworkCategory $selling_network_category ): void {
		$this->selling_network_category = $selling_network_category;
	}
	public function sellingPrice(): ?Price {
		return $this->selling_price;
	}
	public function setSellingPrice( ?Price $selling_price ): void {
		$this->selling_price = $selling_price;
	}

	public static function fromTableRecord( PaidContentTableRecord $record ): self {
		$selling_amount_hex = $record->sellingAmountHex();
		$selling_decimals   = $record->sellingDecimals();
		$selling_symbol     = $record->sellingSymbol();
		if ( null === $selling_amount_hex || null === $selling_decimals || null === $selling_symbol ) {
			$selling_price = null;
		} else {
			$selling_price = new Price( $selling_amount_hex, $selling_decimals, $selling_symbol );
		}

		return new self(
			$record->postID(),
			PaidContent::from( $record->paidContent() ),
			NetworkCategory::from( $record->sellingNetworkCategoryID() ),
			$selling_price,
		);
	}
}
