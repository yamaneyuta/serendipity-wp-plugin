<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Format;

use Cornix\Serendipity\Core\Lib\Security\Validate;
use phpseclib\Math\BigInteger;

class HexFormat {
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

	/**
	 * 16進数文字列を整数に変換します。
	 */
	public static function toInt( string $hex ): int {
		Validate::checkHex( $hex );

		// 引数の値をBigIntegerに変換
		$bi_value = new BigInteger( $hex, 16 );

		// PHP_INT_MAXより大きい値は変換不可
		if ( $bi_value->compare( new BigInteger( PHP_INT_MAX ) ) > 0 ) {
			throw new \InvalidArgumentException( '[F5776072] The value is too large to convert to an integer. hex: ' . $hex );
		}

		return intval( $bi_value->toString() );
	}
}
