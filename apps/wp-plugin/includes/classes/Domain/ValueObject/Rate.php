<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

class Rate {
	/**
	 * Rateインスタンスを生成します。
	 *
	 * @param SymbolPair $symbol_pair 通貨ペア
	 * @param Amount     $amount レートの数量
	 */
	public function __construct( SymbolPair $symbol_pair, Amount $amount ) {
		$this->symbol_pair = $symbol_pair;
		$this->amount      = $amount;
	}

	private SymbolPair $symbol_pair;
	private Amount $amount;

	public function symbolPair(): SymbolPair {
		return $this->symbol_pair;
	}

	public function amount(): Amount {
		return $this->amount;
	}
}
