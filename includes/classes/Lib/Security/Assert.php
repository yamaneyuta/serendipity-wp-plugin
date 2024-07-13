<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Security;

use Cornix\Serendipity\Core\Features\Repository\SellableSymbols;
use Cornix\Serendipity\Core\Lib\Enum\NetworkType;

class Assert {

	public static function isPostID( int $post_ID ): void {
		if ( ! ( new Validator() )->isPostID( $post_ID ) ) {
			throw new \InvalidArgumentException( '[C1D3D3A4] Invalid post ID. - post_ID: ' . $post_ID );
		}
	}

	public static function isAmountHex( string $hex ): void {
		if ( ! ( new Validator() )->isAmountHex( $hex ) ) {
			throw new \InvalidArgumentException( '[9D226886] Invalid hex. - hex: ' . $hex );
		}
	}

	public static function isDecimals( int $decimals ): void {
		if ( ! ( new Validator() )->isDecimals( $decimals ) ) {
			throw new \InvalidArgumentException( '[24FF24F8] Invalid decimals. - decimals: ' . $decimals );
		}
	}

	/** 価格のシンボルとして有効かどうかを返します。(ネットワーク不問) */
	public static function isSymbol( string $symbol ): void {
		// いずれかのネットワークの販売可能なシンボルであればOKの判定
		foreach ( NetworkType::getAll() as $network_type ) {
			if ( ( new Validator() )->isSellableSymbol( $network_type, $symbol ) ) {
				return;
			}
		}
		throw new \InvalidArgumentException( '[925BB232] Invalid symbol. - symbol: ' . $symbol );
	}

	/** 販売価格に使用可能なシンボルかどうかを返します。 */
	public static function isSellableSymbol( string $network_type, string $symbol ): void {
		if ( ! ( new Validator() )->isSellableSymbol( $network_type, $symbol ) ) {
			throw new \InvalidArgumentException( '[CA216343] Invalid selling symbol. - network_type: ' . $network_type . ', symbol: ' . $symbol );
		}
	}

	public static function isNetworkType( string $network_type ): void {
		if ( ! ( new Validator() )->isNetworkType( $network_type ) ) {
			throw new \InvalidArgumentException( '[A6E9242D] Invalid network type. - network_type: ' . $network_type );
		}
	}

	/** アドレスが有効な文字列であることを確認します。 */
	public static function isAddress( string $address ): void {
		if ( ! \Web3\Utils::isAddress( $address ) ) {
			throw new \InvalidArgumentException( '[66BDC040] Invalid address. - address: ' . $address );
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

	public function isAmountHex( string $hex ): bool {
		// 本プラグインにおいてuint256を超える値は扱わない。
		// uint256_max: 0xffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffff
		return $this->isValueHex( $hex ) && strlen( $hex ) <= ( 2 + 64 );
	}

	private function isValueHex( string $hex ): bool {
		// 本プラグインでは、`0x`プレフィックスを含む小文字を、数量を表す16進数表記とする。
		return preg_match( '/^0x[0-9a-f]+$/', $hex ) === 1;
	}

	public function isDecimals( int $decimals ): bool {
		// 小数点以下の桁数は0以上。
		return 0 <= $decimals;
	}

	/** 販売価格に使用可能なシンボルかどうかを返します。 */
	public function isSellableSymbol( string $network_type, string $symbol ): bool {
		// 販売可能なシンボル一覧を取得
		$sellable_symbol = ( new SellableSymbols() )->get( $network_type );

		return in_array( $symbol, $sellable_symbol, true );
	}

	/** 指定された文字列がネットワーク種別かどうかを返します。 */
	public function isNetworkType( string $network_type ): bool {
		return in_array( $network_type, NetworkType::getAll(), true );
	}
}
