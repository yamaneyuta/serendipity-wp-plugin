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
	protected ?int $activation_block_number;
	protected ?int $crawled_block_number;

	public function chainIdValue(): int {
		return $this->chain_id;
	}

	public function addressValue(): string {
		return $this->address;
	}

	public function activationBlockNumberValue(): ?int {
		return $this->activation_block_number;
	}

	public function crawledBlockNumberValue(): ?int {
		return $this->crawled_block_number;
	}
}
