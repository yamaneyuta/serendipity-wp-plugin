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

	public function chainIdValue(): int {
		return $this->chain_id;
	}
	public function addressValue(): string {
		return $this->address;
	}
	public function baseSymbolValue(): string {
		return $this->base_symbol;
	}
	public function quoteSymbolValue(): string {
		return $this->quote_symbol;
	}
}
