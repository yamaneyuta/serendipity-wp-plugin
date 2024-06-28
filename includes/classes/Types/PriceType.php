<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Types;

class PriceType {
	public function __construct( $amount_hex, $decimals, $symbol ) {
		$this->amountHex = $amount_hex;
		$this->decimals  = $decimals;
		$this->symbol    = $symbol;
	}

	public string $amountHex; // GraphQLで使用するためcamelCase

	public int $decimals;

	public string $symbol;
}
