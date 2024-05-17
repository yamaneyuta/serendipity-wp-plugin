<?php

declare(strict_types=1);

namespace Cornix\Serendipity\Core\Utils;

use Cornix\Serendipity\Core\Logger\Logger;
use phpseclib\Math\BigInteger;

class BNConvert {

	/**
	 * 数値(intまたは16進数の文字列)をSolidityと同じ形式の16進数の文字列に変換する。
	 *
	 * @param int|string $val
	 */
	public static function toSolHex( $val ): string {

		if ( is_string( $val ) && false === Strings::starts_with( $val, '0x' ) ) {
			// stringの場合は、0xから始まる16進数の文字列のみ許容。
			// ⇒意図せず10進数の値が文字列型で渡された場合にエラーとすることが目的。
			Logger::error( "val: $val" );
			throw new \Exception( '{5E5F9AE7-DA63-48FE-9791-3194C859B957}' );
		}

		if ( is_integer( $val ) ) {
			$result = '0x' . ( new BigInteger( $val, 10 ) )->toHex();
		} elseif ( strlen( $val ) % 2 === 0 ) {
			$result = $val;
		} else {
			$result = '0x' . ( new BigInteger( $val, 16 ) )->toHex();
		}

		if ( '0x' === $result ) {
			$result = '0x00';
		}

		// solidity内で16進数に変換した場合、すべて小文字になる。
		return strtolower( $result );
	}

	/**
	 * 16進数の文字列を10進数の文字列に変換して返します。
	 *
	 * @param string $hex '0x'から始まる16進数の文字列
	 * @return string
	 */
	public static function hexToDecString( string $hex ): string {
		if ( false === Strings::starts_with( $hex, '0x' ) ) {
			Logger::error( "hex: $hex" );
			throw new \Exception( '{351B06C9-0FF8-43FF-926F-883593835BB5}' );
		}

		return ( new BigInteger( $hex, 16 ) )->toString();
	}

	/**
	 * 10進数の文字列を16進数の文字列に変換して返します。
	 *
	 * @param string $dec_str
	 * @return string
	 */
	public static function decStringToHex( string $dec_str ): string {
		// フォーマットチェック
		if ( false === preg_match( '/^[0-9]+$/', $dec_str ) ) {
			Logger::error( "dec_str: $dec_str" );
			throw new \Exception( '{E091B2BB-5F25-44B6-AF71-4C497EE4C58F}' );
		}

		return self::toSolHex( '0x' . ( new BigInteger( $dec_str, 10 ) )->toHex() );
	}

	/**
	 * 16進数の文字列を指定した長さのバイト長の16進数の文字列に変換(0埋め)して返します。
	 *
	 * @param string $hex
	 * @return string
	 */
	public static function toFixedLengthHex( string $hex, int $bytes ): string {
		// フォーマットチェック
		if ( false === preg_match( '/^0x[0-9a-fA-F]+$/', $hex ) ) {
			Logger::error( "hex: $hex" );
			throw new \Exception( '{746B5937-124A-4815-AC38-B5C5A3C028DF}' );
		}

		$hex = self::toSlimHex( $hex );
		$hex = str_replace( '0x', '', $hex );
		$hex = str_pad( $hex, $bytes * 2, '0', STR_PAD_LEFT );
		return '0x' . $hex;
	}

	/** 0x01のような、0xの後に0が付与されるものを削除します。
	 * 例: 0x01 -> 0x1
	 * 例: 0x00 -> 0x0
	 */
	public static function toSlimHex( string $hex ): string {
		// フォーマットチェック
		if ( false === preg_match( '/^0x[0-9a-fA-F]+$/', $hex ) ) {
			Logger::error( "hex: $hex" );
			throw new \Exception( '{7060DAEC-CB66-451C-B409-01748FCCCE56}' );
		}

		// 正規表現で置換
		return preg_replace( '/^0x0+/', '0x', $hex );
	}
}
