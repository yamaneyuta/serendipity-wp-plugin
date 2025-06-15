<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\ValueObject;

use stdClass;

class PaidContentTableRecord extends TableRecordBase {
	public function __construct( stdClass $record ) {
		$this->import( $record );
	}

	protected int $post_id;
	protected string $paid_content;
	protected ?int $selling_network_category_id;
	protected ?string $selling_amount_hex;
	protected ?int $selling_decimals;
	protected ?string $selling_symbol;

	public function postIdValue(): int {
		return $this->post_id;
	}
	public function paidContentValue(): string {
		return $this->paid_content;
	}
	public function sellingNetworkCategoryIdValue(): ?int {
		return $this->selling_network_category_id;
	}
	public function sellingAmountHexValue(): ?string {
		return $this->selling_amount_hex;
	}
	public function sellingDecimalsValue(): ?int {
		return $this->selling_decimals;
	}
	public function sellingSymbolValue(): ?string {
		return $this->selling_symbol;
	}
}
