<?php

declare(strict_types=1);

namespace Cornix\Serendipity\Core\Currency\Rate;

/**
 * 価格のレートに関する処理を行います。
 */
interface IRate {

	/**
	 * [$symbol]/USDのレート(10**decimals倍)を取得します。
	 *
	 * @param string $symbol
	 * @return string
	 */
	public function getRateAmountHex( string $symbol ): string;

	/**
	 * [$symbol]/USDのレートの小数点以下の桁数を取得します。
	 *
	 * @param string $symbol
	 * @return int
	 */
	public function getRateDecimals( string $symbol ): int;
}
