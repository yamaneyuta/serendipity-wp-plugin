<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Security;

use Cornix\Serendipity\Core\Constant\Config;
use Cornix\Serendipity\Core\Lib\Strings\Strings;
use Cornix\Serendipity\Core\Domain\ValueObject\Symbol;

/**
 * 本システムにおいて`check～`は、引数の値を検証し、不正な値の場合は例外をスローする動作を行います。
 * これはスマートコントラクトのライブラリが`check～`関数で`revert`を行っているものを参考にしています。
 *
 * 参考: Ownable.sol#_checkOwner
 * https://github.com/OpenZeppelin/openzeppelin-contracts/blob/1edc2ae004974ebf053f4eba26b45469937b9381/contracts/access/Ownable.sol#L63-L67
 */
class Validate {

	/** 指定された値が投稿IDであるかどうかを取得します。 */
	private static function isPostID( int $post_ID ): bool {
		// 投稿の状態を取得できれば有効なIDとみなす。
		return false !== get_post_status( $post_ID );
	}

	/**
	 * 投稿IDが有効でない場合は例外をスローします。
	 *
	 * @param int $post_ID 投稿ID
	 * @throws \InvalidArgumentException
	 */
	public static function checkPostID( int $post_ID ): void {
		if ( ! self::isPostID( $post_ID ) ) {
			throw new \InvalidArgumentException( '[C1D3D3A4] Invalid post ID. - post_ID: ' . $post_ID );
		}
	}

	/**
	 * 文字列が16進数表記でない場合は例外をスローします。
	 *
	 * @param string $hex
	 * @throws \InvalidArgumentException
	 */
	public static function checkHex( string $hex ): void {
		if ( ! self::isHex( $hex ) ) {
			throw new \InvalidArgumentException( '[95E1280E] Invalid hex. - hex: ' . $hex );
		}
	}
	/**
	 * 文字列が16進数表記かどうかを返します。
	 */
	public static function isHex( string $hex ): bool {
		// 本プラグインでは、`0x`プレフィックス含む文字列を16進数表記とする。
		return Strings::starts_with( $hex, '0x' ) && \Web3\Utils::isHex( $hex );
	}

	/** 文字列が有効なURLでない場合は例外をスローします。 */
	public static function checkURL( string $url ): void {
		if ( ! self::isUrl( $url ) ) {
			throw new \InvalidArgumentException( '[67D57E5E] Invalid URL. - url: ' . $url );
		}
	}

	/**
	 * 文字列がURLの形式かどうかを返します。
	 */
	public static function isUrl( string $url ): bool {
		return filter_var( $url, FILTER_VALIDATE_URL ) !== false && Strings::starts_with( $url, 'http' );
	}

	/**
	 * 数量として有効な16進数表記でない場合は例外をスローします。
	 *
	 * @param string $hex 16進数表記の数量
	 * @throws InvalidArgumentException
	 */
	public static function checkAmountHex( string $hex ): void {
		if ( ! self::isAmountHex( $hex ) ) {
			throw new \InvalidArgumentException( '[9D226886] Invalid hex. - hex: ' . $hex );
		}
	}
	private static function isAmountHex( string $hex ): bool {
		// 本プラグインにおいてuint256を超える値は扱わない。また、大文字小文字を混在させる必要はないため小文字固定とする。
		// uint256_max: 0xffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffff
		return self::isHex( $hex ) && strlen( $hex ) <= ( 2 + 64 );
	}

	/**
	 * 数量の小数点以下桁数として有効な値でない場合は例外をスローします。
	 *
	 * @param int $decimals 小数点以下桁数
	 * @throws InvalidArgumentException
	 */
	public static function checkDecimals( int $decimals ): void {
		if ( ! self::isDecimals( $decimals ) ) {
			throw new \InvalidArgumentException( '[24FF24F8] Invalid decimals. - decimals: ' . $decimals );
		}
	}
	public static function isDecimals( int $decimals ): bool {
		// 小数点以下の桁数は0以上。
		return 0 <= $decimals;
	}

	/**
	 * 価格の通貨シンボルとして有効な値(ネットワーク不問)でない場合は例外をスローします。
	 *
	 * @param string $symbol 通貨シンボル
	 * @throws InvalidArgumentException
	 */
	public static function checkSymbol( string $symbol ): void {
		if ( ! self::isSymbol( $symbol ) ) {
			throw new \InvalidArgumentException( '[925BB232] Invalid symbol. - symbol: ' . $symbol );
		}
	}
	public static function isSymbol( string $symbol ): bool {
		// 様々な通貨記号が存在するため、空文字列以外であれば有効とする。
		return ! empty( $symbol ) && trim( $symbol ) === $symbol;
	}

	/**
	 * Symbolオブジェクトとして有効な値でない場合は例外をスローします。
	 *
	 * @param Symbol $symbol 通貨シンボル
	 * @throws InvalidArgumentException
	 */
	public static function checkSymbolObject( Symbol $symbol ): void {
		// Symbolオブジェクトの内部値をチェック
		self::checkSymbol( $symbol->value() );
	}

	/** 指定した文字列がブロックのタグ名であるかどうかを判定します。 */
	public static function isBlockTagName( string $block_tag ): bool {
		// 参考: https://www.alchemy.com/overviews/ethereum-commitment-levels
		return in_array( $block_tag, array( 'latest', 'safe', 'finalized' ), true );
	}
	/** 指定した文字列がブロックのタグ名であることをチェックし、不正な文字列の場合は例外をスローします。 */
	public static function checkBlockTagName( string $block_tag ): void {
		if ( ! self::isBlockTagName( $block_tag ) ) {
			throw new \InvalidArgumentException( '[5B634FE3] Invalid tag. tag: ' . $block_tag );
		}
	}


	/** 指定した文字列が請求書に紐づくnonce値のフォーマットであるかどうかを判定します。 */
	public static function isInvoiceNonceValueFormat( string $invoice_nonce_value ): bool {
		// 請求書に紐づくnonceは、128bitのHEX(`0x`プレフィックス無し)文字列
		return preg_match( '/^[0-9a-f]{32}$/i', $invoice_nonce_value ) === 1;
	}

	/** 指定した文字列が請求書に紐づくnonce値のフォーマットでない場合は例外をスローします。 */
	public static function checkInvoiceNonceValueFormat( string $invoice_nonce_value ): void {
		if ( ! self::isInvoiceNonceValueFormat( $invoice_nonce_value ) ) {
			throw new \InvalidArgumentException( '[8EEF9FD6] Invalid invoice nonce value format. - value: ' . $invoice_nonce_value );
		}
	}
}
