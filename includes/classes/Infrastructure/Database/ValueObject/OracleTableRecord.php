<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\ValueObject;

use stdClass;

class OracleTableRecord extends TableRecordBase {
	public function __construct( stdClass $record ) {
		$this->import( $record );
	}

	protected int $chain_id;
	protected string $address;
	protected string $base_symbol;
	protected string $quote_symbol;

	public function chainID(): int {
		return $this->chain_id;
	}
	public function address(): string {
		return $this->address;
	}
	public function baseSymbol(): string {
		return $this->base_symbol;
	}
	public function quoteSymbol(): string {
		return $this->quote_symbol;
	}
}
