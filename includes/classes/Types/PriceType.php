<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Types;

use Cornix\Serendipity\Core\Lib\Security\Judge;

class PriceType {
	public function __construct( $amount_hex, $decimals, $symbol ) {
		Judge::checkAmountHex( $amount_hex );
		Judge::checkDecimals( $decimals );
		Judge::checkSymbol( $symbol );

		$this->amountHex = $amount_hex;
		$this->decimals  = $decimals;
		$this->symbol    = $symbol;
	}

	public string $amountHex; // GraphQLで使用するためcamelCase

	public int $decimals;

	public string $symbol;
}
