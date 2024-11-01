<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Calc;

use phpseclib\Math\BigInteger;

class Hex {
	/**
	 * 整数を'0x'から開始する16進数文字列に変換します。
	 *
	 * @param int|BigInteger $num
	 * @return string
	 */
	public static function from( $num ): string {
		if ( is_int( $num ) ) {
			$num = new BigInteger( $num );
		} elseif ( $num instanceof BigInteger ) {
			// do nothing
		} else {
			throw new \InvalidArgumentException( 'Invalid argument type. ' . gettype( $num ) );
		}

		assert( $num instanceof BigInteger );

		$hex = '0x' . $num->toHex();
		return $hex === '0x' ? '0x00' : $hex;
	}

	/**
	 * 16進数文字列の値が0かどうかを判定します。
	 */
	public static function isZero( string $hex ): bool {
		return preg_match( '/^0x0*$/', $hex ) === 1;
	}
}
