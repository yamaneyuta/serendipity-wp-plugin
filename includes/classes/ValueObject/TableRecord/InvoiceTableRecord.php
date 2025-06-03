<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\ValueObject\TableRecord;

use stdClass;

class InvoiceTableRecord extends TableRecordBase {
	public function __construct( stdClass $record ) {
		$this->import( $record );
	}

	protected string $id;
	protected int $post_id;
	protected int $chain_id;
	protected string $selling_amount_hex;
	protected int $selling_decimals;
	protected string $selling_symbol;
	protected string $seller_address;
	protected string $payment_token_address;
	protected string $payment_amount_hex;
	protected string $consumer_address;

	public function id(): string {
		return $this->id;
	}
	public function postID(): int {
		return $this->post_id;
	}
	public function chainID(): int {
		return $this->chain_id;
	}
	public function sellingAmountHex(): string {
		return $this->selling_amount_hex;
	}
	public function sellingDecimals(): int {
		return $this->selling_decimals;
	}
	public function sellingSymbol(): string {
		return $this->selling_symbol;
	}
	public function sellerAddress(): string {
		return $this->seller_address;
	}
	public function paymentTokenAddress(): string {
		return $this->payment_token_address;
	}
	public function paymentAmountHex(): string {
		return $this->payment_amount_hex;
	}
	public function consumerAddress(): string {
		return $this->consumer_address;
	}
}
