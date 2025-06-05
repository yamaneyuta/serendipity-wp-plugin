<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\ValueObject\TableRecord;

use stdClass;

class UnlockPaywallTransactionTableRecord extends TableRecordBase {
	public function __construct( stdClass $record ) {
		$this->import( $record );
	}

	protected int $chain_id;
	protected int $block_number;
	protected string $transaction_hash;

	public function chainID(): int {
		return $this->chain_id;
	}
	public function blockNumber(): int {
		return $this->block_number;
	}
	public function transactionHash(): string {
		return $this->transaction_hash;
	}
}
