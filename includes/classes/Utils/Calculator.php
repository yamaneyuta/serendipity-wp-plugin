<?php

declare(strict_types=1);

namespace Cornix\Serendipity\Core\Utils;

use phpseclib\Math\BigInteger;
use BN\BN;
use Cornix\Serendipity\Core\Logger\Logger;

class Calculator {

	/**
	 * 16進数の値が0かどうかを返します。
	 */
	public static function isZero( string $hex ): bool {
		return '0' === ( new BigInteger( $hex, 16 ) )->toString();
	}

	/**
	 * @param int|string $val1
	 * @param int|string $val2
	 */
	public static function add( $val1, $val2 ): string {

		$bn1 = self::toBN( $val1 );
		$bn2 = self::toBN( $val2 );

		return '0x' . $bn1->add( $bn2 )->toString( 16 );
	}

	/**
	 * @param int|string $val
	 * @param int|string $val2
	 */
	public static function sub( $val, $val2 ): string {

		$bn1 = self::toBN( $val );
		$bn2 = self::toBN( $val2 );

		return '0x' . $bn1->sub( $bn2 )->toString( 16 );
	}

	/**
	 *
	 * @param int|string $val
	 * @param int|string $val2
	 * @return int 1: val > val2, 0: val == val2, -1: val < val2
	 */
	public static function compare( $val, $val2 ): int {

		$bn1 = self::toBN( $val );
		$bn2 = self::toBN( $val2 );

		return $bn1->cmp( $bn2 );
	}

	/**
	 * @param int|string $val
	 */
	private static function toBN( $val ): BN {
		if ( is_integer( $val ) ) {
			return new BN( $val, 10 );
		} elseif ( is_string( $val ) ) {
			// フォーマットチェック
			if ( false === Strings::starts_with( $val, '0x' ) ) {
				Logger::error( "val: $val" );
				throw new \Exception( '{6F94CC49-8399-4D6B-B068-C798C292D009}' );
			}
			return new BN( substr( $val, 2 ), 16 );
		} else {
			Logger::error( 'val: ' . var_export( $val, true ) );
			throw new \Exception( '{EDFB474F-8FD4-4589-AFD0-2D64E5679876}' );
		}
	}


	/**
	 * パーセントでの値を割合に変換します。
	 * 例) amount: 100, decimals: 1 => 10% => 0.1
	 *     to_decimalsが6の時、戻り値は`0x0186a0`(100_000の16進数文字列)。
	 *
	 * @return string hex value.
	 */
	public static function percentToRatio( string $amount_hex, int $decimals, int $to_decimals ): string {

		$diff_decimals = $to_decimals - $decimals - 2;
		if ( $diff_decimals === 0 ) {
			return $amount_hex;
		}

		$amount_str = BNConvert::hexToDecString( $amount_hex );

		if ( $diff_decimals > 0 ) {
			return BNConvert::decStringToHex( bcmul( $amount_str, bcpow( '10', (string) $diff_decimals, 0 ) ) );
		} else {
			return BNConvert::decStringToHex( bcdiv( $amount_str, bcpow( '10', (string) -$diff_decimals, 0 ) ) );
		}
	}
}
