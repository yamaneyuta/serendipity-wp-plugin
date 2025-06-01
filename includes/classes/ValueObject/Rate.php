<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\ValueObject;

use Cornix\Serendipity\Core\Lib\Security\Validate;

class Rate {
	/**
	 * Rateインスタンスを生成します。
	 *
	 * @param SymbolPair $symbol_pair 通貨ペア
	 * @param string     $amount_hex レートの数量(0xプレフィックス付きの16進数)
	 * @param int        $decimals レートの小数点以下桁数
	 */
	public function __construct( SymbolPair $symbol_pair, string $amount_hex, int $decimals ) {
		Validate::checkAmountHex( $amount_hex );
		Validate::checkDecimals( $decimals );

		$this->symbol_pair = $symbol_pair;
		$this->amount_hex  = $amount_hex;
		$this->decimals    = $decimals;
	}

	private SymbolPair $symbol_pair;
	private string $amount_hex;
	private int $decimals;

	public function symbolPair(): SymbolPair {
		return $this->symbol_pair;
	}

	public function amountHex(): string {
		return $this->amount_hex;
	}

	public function decimals(): int {
		return $this->decimals;
	}
}
