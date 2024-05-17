<?php
// 厳格な型検査モード。(php7.0以上)
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Utils;

class Strings {
	private static $IS_MBSTRING_ENABLED = null;
	private static function is_mbstring_enabled(): bool {
		if ( self::$IS_MBSTRING_ENABLED === null ) {
			self::$IS_MBSTRING_ENABLED = extension_loaded( 'mbstring' );
		}
		return self::$IS_MBSTRING_ENABLED;
	}

	public static function substr(
		string $string,
		int $start,
		?int $length = null,
		?string $encoding = null
	) {
		if ( self::is_mbstring_enabled() ) {
			// $encodingがnullableになったのはphp8.0から
			if ( is_null( $encoding ) ) {
				return mb_substr( $string, $start, $length );
			} else {
				return mb_substr( $string, $start, $length, $encoding );
			}
		} else {
			return substr( $string, $start, $length );
		}
	}


	public static function strpos(
		string $haystack,
		string $needle,
		int $offset = 0
	) {
		if ( self::is_mbstring_enabled() ) {
			return mb_strpos( $haystack, $needle, $offset );
		} else {
			return strpos( $haystack, $needle, $offset );
		}
	}

	/**
	 * 文字列内の指定した文字列の出現位置をすべて検索する。(独自実装)
	 *
	 * @return int[]
	 */
	public static function all_strpos( string $haystack, string $needle ): array {
		$offset    = 0;
		$positions = array();
		while ( ( $pos = self::strpos( $haystack, $needle, $offset ) ) !== false ) {
			$positions[] = $pos;
			$offset      = $pos + 1;
		}
		return $positions;
	}


	/**
	 * @param string $string
	 * @return int|false
	 */
	public static function strlen( string $string ) {
		if ( self::is_mbstring_enabled() ) {
			return mb_strlen( $string );
		} else {
			return strlen( $string );
		}
	}

	public static function starts_with( string $string, string $prefix ): bool {
		// str_starts_with()はphp8.0から
		return self::substr( $string, 0, self::strlen( $prefix ) ) === $prefix;
	}
}
