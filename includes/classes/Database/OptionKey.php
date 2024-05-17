<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Database;

use Cornix\Serendipity\Core\Utils\Constants;

class OptionKey {

	/**
	 * optionsテーブルに保存する時のキー名に付与するプレフィックスを取得します。
	 *
	 * @return string
	 */
	private static function getPrefix(): string {
		if ( self::$_prefix === null ) {
			self::$_prefix = Constants::get( 'prefix.optionKey' );
		}
		return self::$_prefix;
	}
	/** @var string|null */
	private static $_prefix = null;

	/**
	 * optionsテーブルに保存する時のキー名を取得します。
	 *
	 * @param string $key
	 * @return string
	 */
	private static function getOptionKey( string $key ): string {
		return self::getPrefix() . $key;
	}

	/**
	 * RPC URL情報を保存または取得する時のキー名を取得します。
	 *
	 * @return string
	 */
	public static function rpcUrls(): string {
		return self::getOptionKey( 'rpc_urls' );
	}

	/**
	 * 購入可能なチェーンIDを保存する時のキー名を取得します。
	 *
	 * @return string
	 */
	public static function purchasableChainIds(): string {
		return self::getOptionKey( 'purchasable_chain_ids' );
	}

	/**
	 * ブロックが確定したとみなす承認数を保存する時のキー名を取得します。
	 *
	 * @return string
	 */
	public static function txConfirmations(): string {
		return self::getOptionKey( 'tx_confirmations' );
	}

	/**
	 * ゲストユーザーが購入可能な通貨シンボルの一覧を保存する時のキー名を取得します。
	 *
	 * @return string
	 */
	public static function payableSymbols(): string {
		return self::getOptionKey( 'payable_symbols' );
	}
}
