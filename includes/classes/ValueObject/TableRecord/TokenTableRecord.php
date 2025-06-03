<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\ValueObject\TableRecord;

use stdClass;

class TokenTableRecord extends TableRecordBase {
	public function __construct( stdClass $record ) {
		$this->import( $record );
	}

	protected int $chain_id;
	protected string $address;
	protected string $symbol;
	protected int $decimals;
	protected bool $is_payable;

	public function chainID(): int {
		return $this->chain_id;
	}
	public function address(): string {
		return $this->address;
	}
	public function symbol(): string {
		return $this->symbol;
	}
	public function decimals(): int {
		return $this->decimals;
	}
	public function isPayable(): bool {
		return $this->is_payable;
	}
}
