<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\ValueObject\TableRecord;

use stdClass;

class InvoiceTableRecord {
	public function __construct( stdClass $record ) {
		$this->id                    = $record->id;
		$this->post_id               = (int) $record->post_id;
		$this->chain_id              = (int) $record->chain_id;
		$this->selling_amount_hex    = $record->selling_amount_hex;
		$this->selling_decimals      = (int) $record->selling_decimals;
		$this->selling_symbol        = $record->selling_symbol;
		$this->seller_address        = $record->seller_address;
		$this->payment_token_address = $record->payment_token_address;
		$this->payment_amount_hex    = $record->payment_amount_hex;
		$this->consumer_address      = $record->consumer_address;
	}

	public string $id;
	public int $post_id;
	public int $chain_id;
	public string $selling_amount_hex;
	public int $selling_decimals;
	public string $selling_symbol;
	public string $seller_address;
	public string $payment_token_address;
	public string $payment_amount_hex;
	public string $consumer_address;
}
