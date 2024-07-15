<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Calc;

use phpseclib\Math\BigInteger;

class Hex {
	/**
	 * BigIntegerを'0x'から開始する16進数文字列に変換します。
	 */
	public static function fromBigInteger( BigInteger $big_integer ): string {
		$hex = '0x' . $big_integer->toHex();
		return $hex === '0x' ? '0x00' : $hex;
	}
}
