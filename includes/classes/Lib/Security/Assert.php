<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Security;

class Assert {

	public static function isPostID( int $post_ID ): void {
		if ( ! ( new Validator() )->isPostID( $post_ID ) ) {
			throw new \InvalidArgumentException( '[C1D3D3A4] Invalid post ID. - post_ID: ' . $post_ID );
		}
	}

	public static function isHex( string $hex ): void {
		if ( ! ( new Validator() )->isHex( $hex ) ) {
			throw new \InvalidArgumentException( '[9D226886] Invalid hex. - hex: ' . $hex );
		}
	}

	public static function isDecimals( int $decimals ): void {
		if ( ! ( new Validator() )->isDecimals( $decimals ) ) {
			throw new \InvalidArgumentException( '[24FF24F8] Invalid decimals. - decimals: ' . $decimals );
		}
	}

	public static function isSymbol( string $symbol ): void {
		if ( ! ( new Validator() )->isSymbol( $symbol ) ) {
			throw new \InvalidArgumentException( '[925BB232] Invalid symbol. - symbol: ' . $symbol );
		}
	}
}

/**
 * @internal
 */
class Validator {

	public function isPostID( int $post_ID ): bool {
		// 投稿の状態を取得できれば有効なIDとみなす。
		return false !== get_post_status( $post_ID );
	}

	public function isHex( string $hex ): bool {
		// 本プラグインでは、`0x`プレフィックスを含む小文字をHEXとして扱います。
		return preg_match( '/^0x[0-9a-f]+$/', $hex ) === 1;
	}

	public function isDecimals( int $decimals ): bool {
		// 小数点以下の桁数は0以上。
		return 0 <= $decimals;
	}

	public function isSymbol( string $symbol ): bool {
		// 一旦、大文字の3文字から5文字をシンボルとして扱う。
		// TODO: jsonファイル等から取得して比較するように変更する。
		return preg_match( '/^[A-Z]{3,5}$/', $symbol ) === 1;
	}
}
