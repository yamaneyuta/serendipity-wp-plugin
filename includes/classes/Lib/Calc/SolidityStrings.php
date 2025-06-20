<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Calc;

use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use phpseclib\Math\BigInteger;

/**
 * `@openzeppelin/contracts/utils/Strings.sol`の`Strings`コントラクトの関数と同じ結果を取得するためのクラス
 */
class SolidityStrings {
	/**
	 * 指定された値をSolidityで扱う16進数文字列に変換します。
	 */
	public static function valueToHexString( $value ): string {
		if ( is_int( $value ) ) {
			$value = new BigInteger( $value, 10 );
		} elseif ( is_string( $value ) && preg_match( '/^0x[0-9a-fA-F]*$/', $value ) ) {
			$value = new BigInteger( $value, 16 );
		} elseif ( $value instanceof BigInteger ) {
			// do nothing
		} else {
			throw new \InvalidArgumentException( '[8C48698E] Invalid argument type. ' . gettype( $value ) );
		}

		assert( $value instanceof BigInteger );

		$hex    = '0x' . $value->toHex();
		$result = $hex === '0x' ? '0x00' : $hex;

		assert( strlen( $result ) % 2 === 0 );  // 結果は偶数桁
		assert( preg_match( '/^0x[0-9a-f]+$/', $result ) === 1 );   // 16進数文字列はすべて小文字

		return $result;
	}

	/**
	 * 指定されたアドレスをSolidityで扱う16進数文字列に変換します。
	 */
	public static function addressToHexString( Address $address ): string {
		if ( ! preg_match( '/^0x[0-9a-fA-F]{0,40}$/', $address->value() ) ) {
			throw new \InvalidArgumentException( '[A862D0B5] Invalid address format. address: ' . $address );
		}

		$address_text = self::valueToHexString( $address->value() );

		// SolidityにおけるtoHexString(address)は42文字の長さ
		assert( strlen( $address_text ) <= 42 );
		if ( strlen( $address_text ) !== 42 ) {
			$diff         = 42 - strlen( $address_text );
			$replace_str  = '0x' . str_repeat( '0', $diff );
			$address_text = str_replace( '0x', $replace_str, $address_text );
		}
		assert( preg_match( '/^0x[0-9a-f]{40}$/', $address_text ) === 1 );   // 16進数文字列はすべて小文字、桁数は40

		return $address_text;
	}
}
