<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\ValueObject;

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
	protected string $nonce;

	public function idValue(): string {
		return $this->id;
	}
	public function postIdValue(): int {
		return $this->post_id;
	}
	public function chainIdValue(): int {
		return $this->chain_id;
	}
	public function sellingAmountHexValue(): string {
		return $this->selling_amount_hex;
	}
	public function sellingDecimalsValue(): int {
		return $this->selling_decimals;
	}
	public function sellingSymbolValue(): string {
		return $this->selling_symbol;
	}
	public function sellerAddressValue(): string {
		return $this->seller_address;
	}
	public function paymentTokenAddressValue(): string {
		return $this->payment_token_address;
	}
	public function paymentAmountHexValue(): string {
		return $this->payment_amount_hex;
	}
	public function consumerAddressValue(): string {
		return $this->consumer_address;
	}
	public function nonceValue(): string {
		return $this->nonce;
	}
}
