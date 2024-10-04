<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Types;

use Cornix\Serendipity\Core\Lib\Security\Judge;

class PriceType {
	public function __construct( $amount_hex, $decimals, $symbol ) {
		Judge::checkAmountHex( $amount_hex );
		Judge::checkDecimals( $decimals );
		Judge::checkSymbol( $symbol );

		$this->amount_hex = $amount_hex;
		$this->decimals   = $decimals;
		$this->symbol     = $symbol;
	}

	/** 金額の数量(0xプレフィックス付きの16進数) */
	public string $amount_hex;

	/** 金額の小数点以下桁数 */
	public int $decimals;

	/** 通貨記号(`USD`, `ETH`等)。記号(`$`等)不可。 */
	public string $symbol;
}
