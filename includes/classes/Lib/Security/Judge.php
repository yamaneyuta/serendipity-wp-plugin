<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Security;

use Cornix\Serendipity\Core\Lib\Repository\ChainData;
use Cornix\Serendipity\Core\Lib\Repository\PayableTokens;
use Cornix\Serendipity\Core\Lib\Repository\SellableSymbols;
use Cornix\Serendipity\Core\Lib\Repository\SellerTerms;
use Cornix\Serendipity\Core\Lib\Strings\Strings;
use Cornix\Serendipity\Core\Lib\Web3\Ethers;
use Cornix\Serendipity\Core\Types\NetworkCategory;
use Cornix\Serendipity\Core\Types\TokenType;

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
	public static function checkHasAdminRole(): void {
		if ( ! ( new Access() )->isAdministrator() ) {
			throw new \LogicException( '[D10C401C] You do not have permission to access this feature. current user ID: ' . get_current_user_id() );
		}
	}

	/**
	 * 現在アクセスしているユーザーが編集者以上の権限を持っていない場合は例外をスローします。
	 */
	public static function checkHasEditableRole(): void {
		if ( ! ( new Access() )->canCurrentUserCreatePost() ) {
			throw new \LogicException( '[9FB8121B] You do not have permission to access this feature. user ID: ' . get_current_user_id() );
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

	/**
	 * 文字列がURLの形式かどうかを返します。
	 */
	public static function isUrl( string $url ): bool {
		return filter_var( $url, FILTER_VALIDATE_URL ) !== false && Strings::starts_with( $url, 'http' );
	}

	/** チェーンIDが正常でない場合は例外をスローします。 */
	public static function checkChainID( int $chain_ID ): void {
		if ( ! self::isChainID( $chain_ID ) ) {
			throw new \InvalidArgumentException( '[84C80B37] Invalid chain ID. - chain ID: ' . $chain_ID );
		}
	}
	/** 指定された値がチェーンIDとして有効かどうかを返します。 */
	public static function isChainID( int $chain_ID ): bool {
		return in_array( $chain_ID, ( new ChainData() )->allIDs(), true );
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
	 * 購入者が支払可能なトークンでない場合は例外をスローします。
	 *
	 * @param TokenType $token
	 * @throws \InvalidArgumentException
	 */
	public static function checkPayableToken( TokenType $token ): void {
		if ( ! Validator::isPayableToken( $token ) ) {
			throw new \InvalidArgumentException( '[30970153] Invalid payable token. - chain id: ' . $token->chainID() . ', address: ' . $token->address() );
		}
	}

	/**
	 * アドレスとして有効な値でない場合は例外をスローします。
	 *
	 * @param string $address アドレス(ウォレットアドレス/コントラクトアドレス)
	 * @throws InvalidArgumentException
	 */
	public static function checkAddress( string $address ): void {
		if ( ! self::isAddress( $address ) ) {
			throw new \InvalidArgumentException( '[66BDC040] Invalid address. - address: ' . $address );
		}
	}
	/**
	 * アドレスとして有効な値かどうかを返します。
	 */
	public static function isAddress( string $address ): bool {
		return Ethers::isAddress( $address );
	}

	/**
	 * 引数として渡されたバージョンが現在の販売者向け利用規約バージョンと一致しない場合は例外をスローします。
	 *
	 * @param int $seller_terms_version
	 * @return void
	 * @throws \InvalidArgumentException
	 */
	public static function checkCurrentSellerTermsVersion( int $seller_terms_version ): void {
		if ( ! self::isCurrentSellerTermsVersion( $seller_terms_version ) ) {
			throw new \InvalidArgumentException( '[93398C5C] Invalid version. seller_terms_version: ' . $seller_terms_version );
		}
	}
	private static function isCurrentSellerTermsVersion( int $seller_terms_version ): bool {
		$current_version = ( new SellerTerms() )->currentVersion();  // 現在の販売者向け利用規約バージョン
		return $seller_terms_version === $current_version;
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
}

/**
 * @internal
 */
class Validator {

	public static function isPostID( int $post_ID ): bool {
		// 投稿の状態を取得できれば有効なIDとみなす。
		return false !== get_post_status( $post_ID );
	}

	/** 購入者が支払可能なトークンかどうかを返します。 */
	public static function isPayableToken( TokenType $token ): bool {
		// 管理者が保存した、購入者が支払時に使用可能なトークン一覧を取得
		$payable_tokens = ( new PayableTokens() )->get( $token->chainID() );

		return in_array( $token, $payable_tokens, true );
	}
}
