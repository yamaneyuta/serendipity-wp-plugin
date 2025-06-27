<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\ValueObject;

use stdClass;

class UnlockPaywallTransactionTableRecord extends TableRecordBase {
	public function __construct( stdClass $record ) {
		$this->import( $record );
	}

	protected int $chain_id;
	protected int $block_number;
	protected string $transaction_hash;

	public function chainIdValue(): int {
		return $this->chain_id;
	}
	public function blockNumberValue(): int {
		return $this->block_number;
	}
	public function transactionHashValue(): string {
		return $this->transaction_hash;
	}
}
