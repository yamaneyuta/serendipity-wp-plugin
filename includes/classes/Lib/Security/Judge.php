<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Security;

use Cornix\Serendipity\Core\Lib\Repository\ChainData;
use Cornix\Serendipity\Core\Lib\Repository\PayableChainIDs;
use Cornix\Serendipity\Core\Lib\Repository\PayableSymbols;
use Cornix\Serendipity\Core\Lib\Repository\SellableSymbols;
use Cornix\Serendipity\Core\Types\NetworkCategory;

/**
 * 本システムにおいて`check～`は、引数の値を検証し、不正な値の場合は例外をスローする動作を行います。
 * これはスマートコントラクトのライブラリが`check～`関数で`revert`を行っているものを参考にしています。
 *
 * 参考: Ownable.sol#_checkOwner
 * https://github.com/OpenZeppelin/openzeppelin-contracts/blob/1edc2ae004974ebf053f4eba26b45469937b9381/contracts/access/Ownable.sol#L63-L67
 */
class Judge {

	/**
	 * 現在アクセスしているユーザーが管理者権限を持っていない場合は例外をスローします。
	 */
	public static function checkIsAdministrator(): void {
		if ( ! ( new Access() )->isAdministrator() ) {
			throw new \LogicException( '[D10C401C] You are not an administrator. current user ID: ' . get_current_user_id() );
		}
	}

	/**
	 * 投稿IDが有効でない場合は例外をスローします。
	 *
	 * @param int $post_ID 投稿ID
	 * @throws \InvalidArgumentException
	 */
	public static function checkPostID( int $post_ID ): void {
		if ( ! Validator::isPostID( $post_ID ) ) {
			throw new \InvalidArgumentException( '[C1D3D3A4] Invalid post ID. - post_ID: ' . $post_ID );
		}
	}

	/**
	 * 数量として有効な16進数表記でない場合は例外をスローします。
	 *
	 * @param string $hex 16進数表記の数量
	 * @throws InvalidArgumentException
	 */
	public static function checkAmountHex( string $hex ): void {
		if ( ! Validator::isAmountHex( $hex ) ) {
			throw new \InvalidArgumentException( '[9D226886] Invalid hex. - hex: ' . $hex );
		}
	}

	/**
	 * 数量の小数点以下桁数として有効な値でない場合は例外をスローします。
	 *
	 * @param int $decimals 小数点以下桁数
	 * @throws InvalidArgumentException
	 */
	public static function checkDecimals( int $decimals ): void {
		if ( ! Validator::isDecimals( $decimals ) ) {
			throw new \InvalidArgumentException( '[24FF24F8] Invalid decimals. - decimals: ' . $decimals );
		}
	}

	/**
	 * 価格の通貨シンボルとして有効な値(ネットワーク不問)でない場合は例外をスローします。
	 *
	 * @param string $symbol 通貨シンボル
	 * @throws InvalidArgumentException
	 */
	public static function checkSymbol( string $symbol ): void {
		if ( ! Validator::isSymbol( $symbol ) ) {
			throw new \InvalidArgumentException( '[925BB232] Invalid symbol. - symbol: ' . $symbol );
		}
	}

	/**
	 * 販売価格に使用可能な通貨シンボルでない場合は例外をスローします。
	 *
	 * @param NetworkCategory $network_category ネットワークカテゴリ
	 * @param string          $symbol 通貨シンボル
	 * @throws InvalidArgumentException
	 * @deprecated 現在未使用のため暫定的にマーク
	 */
	public static function checkSellableSymbol( NetworkCategory $network_category, string $symbol ): void {
		if ( ! Validator::isSellableSymbol( $network_category, $symbol ) ) {
			throw new \InvalidArgumentException( '[CA216343] Invalid selling symbol. - network_category: ' . $network_category . ', symbol: ' . $symbol );
		}
	}

	/**
	 * 購入者が支払可能なチェーンIDでない場合は例外をスローします。
	 *
	 * @param int $chain_ID
	 * @throws \InvalidArgumentException
	 */
	public static function checkPayableChainID( int $chain_ID ): void {
		if ( ! Validator::isPayableChainID( $chain_ID ) ) {
			throw new \InvalidArgumentException( '[AB85623D] Invalid chain ID. - chain_ID: ' . $chain_ID );
		}
	}

	/**
	 * 購入者が支払可能な通貨シンボル(トークン)でない場合は例外をスローします。
	 *
	 * @param int    $chain_ID チェーンID
	 * @param string $symbol   通貨シンボル(トークン)
	 * @throws \InvalidArgumentException
	 */
	public static function checkPayableSymbol( int $chain_ID, string $symbol ): void {
		if ( ! Validator::isPayableSymbol( $chain_ID, $symbol ) ) {
			throw new \InvalidArgumentException( '[30970153] Invalid symbol. - chain_ID: ' . $chain_ID . ', symbol: ' . $symbol );
		}
	}

	/**
	 * アドレスとして有効な値でない場合は例外をスローします。
	 *
	 * @param string $address アドレス(ウォレットアドレス/コントラクトアドレス)
	 * @throws InvalidArgumentException
	 */
	public static function checkAddress( string $address ): void {
		if ( ! Validator::isAddress( $address ) ) {
			throw new \InvalidArgumentException( '[66BDC040] Invalid address. - address: ' . $address );
		}
	}
}

/**
 * @internal
 */
class Validator {

	public static function isPostID( int $post_ID ): bool {
		// 投稿の状態を取得できれば有効なIDとみなす。
		return false !== get_post_status( $post_ID );
	}

	public static function isAmountHex( string $hex ): bool {
		// 本プラグインにおいてuint256を超える値は扱わない。
		// uint256_max: 0xffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffff
		return self::isValueHex( $hex ) && strlen( $hex ) <= ( 2 + 64 );
	}

	private static function isValueHex( string $hex ): bool {
		// 本プラグインでは、`0x`プレフィックスを含む小文字を、数量を表す16進数表記とする。
		return preg_match( '/^0x[0-9a-f]+$/', $hex ) === 1;
	}

	public static function isDecimals( int $decimals ): bool {
		// 小数点以下の桁数は0以上。
		return 0 <= $decimals;
	}

	public static function isSymbol( string $symbol ): bool {
		// いずれかのネットワークの販売可能なシンボルであればOKとする
		if ( self::isSellableSymbol( NetworkCategory::mainnet(), $symbol ) ) {
			return true;
		}
		if ( self::isSellableSymbol( NetworkCategory::testnet(), $symbol ) ) {
			return true;
		}
		if ( self::isSellableSymbol( NetworkCategory::privatenet(), $symbol ) ) {
			return true;
		}
		return false;
	}

	/** 販売価格に使用可能なシンボルかどうかを返します。 */
	public static function isSellableSymbol( NetworkCategory $network_category, string $symbol ): bool {
		// 販売可能なシンボル一覧を取得
		$sellable_symbol = ( new SellableSymbols() )->get( $network_category );

		return in_array( $symbol, $sellable_symbol, true );
	}

	/** 購入者が支払可能なチェーンIDかどうかを返します。 */
	public static function isPayableChainID( int $chain_ID ): bool {
		// 管理者が保存した、購入者が支払可能なチェーンID一覧を取得
		$network_category  = ( new ChainData() )->getNetworkCategory( $chain_ID );
		$payable_chain_ids = ( new PayableChainIDs() )->get( $network_category );

		return in_array( $chain_ID, $payable_chain_ids, true );
	}

	/** 購入者が支払可能なトークンかどうかを返します。 */
	public static function isPayableSymbol( int $chain_ID, string $symbol ): bool {
		// 管理者が保存した、購入者が支払時に使用可能なトークン一覧を取得
		$payable_symbols = ( new PayableSymbols() )->get( $chain_ID );

		return in_array( $symbol, $payable_symbols, true );
	}

	public static function isAddress( string $address ): bool {
		return \Web3\Utils::isAddress( $address );
	}
}
