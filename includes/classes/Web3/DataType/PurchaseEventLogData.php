<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Web3\DataType;

class PurchaseEventLogData {
	/** @var int */
	public $chain_id;

	/** @var string */
	public $log_index_hex;

	/** @var string */
	public $transaction_index_hex;

	/** @var string */
	public $transaction_hash_hex;

	/** @var string */
	public $block_hash_hex;

	/** @var string */
	public $block_number_hex;

	// ----- topics -----
	/** @var string */
	public $signer_hex;

	// ----- decoded data -----
	/** @var string */
	public $ticket_id_hex;

	/** @var string */
	public $from_hex;

	/** @var string */
	public $to_hex;

	/** @var string */
	public $symbol;

	/** @var string */
	public $profit_hex;

	/** @var string */
	public $commission_hex;

	/** @var string */
	public $affiliate_hex;

	/** @var string */
	public $affiliate_account_hex;

	public function __construct(
		int $chain_id,
		string $log_index_hex,
		string $transaction_index_hex,
		string $transaction_hash_hex,
		string $block_hash_hex,
		string $block_number_hex,
		// ----- topics -----
		string $signer_hex,
		// ----- decoded data -----
		string $ticket_id_hex,
		string $from_hex,
		string $to_hex,
		string $symbol,
		string $profit_hex,
		string $commission_hex,
		string $affiliate_hex,
		string $affiliate_account_hex
	) {
		$this->chain_id              = $chain_id;
		$this->log_index_hex         = $log_index_hex;
		$this->transaction_index_hex = $transaction_index_hex;
		$this->transaction_hash_hex  = $transaction_hash_hex;
		$this->block_hash_hex        = $block_hash_hex;
		$this->block_number_hex      = $block_number_hex;
		// ----- topics -----
		$this->signer_hex = $signer_hex;
		// ----- decoded data -----
		$this->ticket_id_hex         = $ticket_id_hex;
		$this->from_hex              = $from_hex;
		$this->to_hex                = $to_hex;
		$this->symbol                = $symbol;
		$this->profit_hex            = $profit_hex;
		$this->commission_hex        = $commission_hex;
		$this->affiliate_hex         = $affiliate_hex;
		$this->affiliate_account_hex = $affiliate_account_hex;
	}
}
