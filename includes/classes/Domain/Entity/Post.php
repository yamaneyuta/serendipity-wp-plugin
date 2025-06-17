<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Entity;

use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategoryID;
use Cornix\Serendipity\Core\Domain\ValueObject\Price;

class Post {

	public function __construct( int $post_id, ?PaidContent $paid_content, ?NetworkCategoryID $selling_network_category_id, ?Price $selling_price ) {
		$this->post_id                     = $post_id;
		$this->paid_content                = $paid_content;
		$this->selling_network_category_id = $selling_network_category_id;
		$this->selling_price               = $selling_price;
	}

	private int $post_id;
	private ?PaidContent $paid_content;
	private ?Price $selling_price;
	private ?NetworkCategoryID $selling_network_category_id;

	public function id(): int {
		return $this->post_id;
	}
	public function paidContent(): ?PaidContent {
		return $this->paid_content;
	}
	public function sellingNetworkCategoryID(): ?NetworkCategoryID {
		return $this->selling_network_category_id;
	}
	public function sellingPrice(): ?Price {
		return $this->selling_price;
	}

	public function setPaidContent( PaidContent $paid_content, ?NetworkCategoryID $selling_network_category_id, ?Price $selling_price ): void {
		$this->paid_content                = $paid_content;
		$this->selling_network_category_id = $selling_network_category_id;
		$this->selling_price               = $selling_price;
	}

	public function deletePaidContent(): void {
		$this->paid_content                = null;
		$this->selling_network_category_id = null;
		$this->selling_price               = null;
	}
}
