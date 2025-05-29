<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\ValueObject;

use Cornix\Serendipity\Core\Lib\Security\Judge;

class SymbolPair {
	public function __construct( string $base_symbol, string $quote_symbol ) {
		Judge::checkSymbol( $base_symbol );
		Judge::checkSymbol( $quote_symbol );

		$this->base_symbol  = $base_symbol;
		$this->quote_symbol = $quote_symbol;
	}

	private string $base_symbol;
	private string $quote_symbol;

	public function base(): string {
		return $this->base_symbol;
	}

	public function quote(): string {
		return $this->quote_symbol;
	}
}
