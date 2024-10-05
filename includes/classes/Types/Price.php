<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Types;

use Cornix\Serendipity\Core\Lib\Security\Judge;

class Price {
	public function __construct( $amount_hex, $decimals, $symbol ) {
		Judge::checkAmountHex( $amount_hex );
		Judge::checkDecimals( $decimals );
		Judge::checkSymbol( $symbol );

		$this->amount_hex = $amount_hex;
		$this->decimals   = $decimals;
		$this->symbol     = $symbol;
	}

	private string $amount_hex;
	private int $decimals;
	private string $symbol;

	/** 金額の数量(0xプレフィックス付きの16進数)を取得します。 */
	public function amountHex(): string {
		return $this->amount_hex;
	}

	/** 金額の小数点以下桁数を取得します。 */
	public function decimals(): int {
		return $this->decimals;
	}

	/** 通貨記号(`USD`, `ETH`等)を取得します。記号(`$`等)ではない。 */
	public function symbol(): string {
		return $this->symbol;
	}
}
