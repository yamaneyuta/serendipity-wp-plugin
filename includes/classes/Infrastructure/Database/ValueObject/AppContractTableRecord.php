<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\ValueObject;

use stdClass;

class AppContractTableRecord extends TableRecordBase {
	public function __construct( stdClass $record ) {
		$this->import( $record );
	}

	protected int $chain_id;
	protected string $address;
	protected int $activation_block_number;
	protected int $crawled_block_number;

	public function chainID(): int {
		return $this->chain_id;
	}

	public function address(): string {
		return $this->address;
	}

	public function activationBlockNumber(): int {
		return $this->activation_block_number;
	}

	public function crawledBlockNumber(): int {
		return $this->crawled_block_number;
	}
}
