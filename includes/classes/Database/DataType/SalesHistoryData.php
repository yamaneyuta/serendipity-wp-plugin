<?php

declare(strict_types=1);

namespace Cornix\Serendipity\Core\Database\DataType;

class SalesHistoryData {
	/** @var int */
	public $ticket_created_at_unix;
	/** @var int */
	public $chain_id;
	/** @var int */
	public $post_id;
	/** @var string */
	public $post_title;
	/** @var string */
	public $selling_amount_hex;
	/** @var int */
	public $selling_decimals;
	/** @var string */
	public $selling_symbol;
	/** @var string */
	public $payment_symbol;
	/** @var int */
	public $payment_decimals;
	/** @var string */
	public $profit_amount_hex;
	/** @var string */
	public $fee_amount_hex;
	/** @var string */
	public $affiliate_amount_hex;
	/** @var string */
	public $from_address;
	/** @var string */
	public $to_address;
	/** @var string */
	public $affiliate_address;
	/** @var string */
	public $transaction_hash_hex;

	public function __construct(
		int $ticket_created_at_unix,
		int $chain_id,
		int $post_id,
		string $post_title,
		string $selling_amount_hex,
		int $selling_decimals,
		string $selling_symbol,
		string $payment_symbol,
		int $payment_decimals,
		string $profit_amount_hex,
		string $fee_amount_hex,
		string $affiliate_amount_hex,
		string $from_address,
		string $to_address,
		string $affiliate_address,
		string $transaction_hash_hex
	) {
		$this->ticket_created_at_unix = $ticket_created_at_unix;
		$this->chain_id               = $chain_id;
		$this->post_id                = $post_id;
		$this->post_title             = $post_title;
		$this->selling_amount_hex     = $selling_amount_hex;
		$this->selling_decimals       = $selling_decimals;
		$this->selling_symbol         = $selling_symbol;
		$this->payment_symbol         = $payment_symbol;
		$this->payment_decimals       = $payment_decimals;
		$this->profit_amount_hex      = $profit_amount_hex;
		$this->fee_amount_hex         = $fee_amount_hex;
		$this->affiliate_amount_hex   = $affiliate_amount_hex;
		$this->from_address           = $from_address;
		$this->to_address             = $to_address;
		$this->affiliate_address      = $affiliate_address;
		$this->transaction_hash_hex   = $transaction_hash_hex;
	}
}
