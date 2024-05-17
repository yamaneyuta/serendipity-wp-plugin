<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Utils;

class TypeValidator {

	/**
	 * $valueが投稿IDの型であるかどうかを返します。
	 *
	 * @param int $post_id 投稿ID
	 * @return bool 投稿IDの型である場合はtrue
	 */
	public static function isPostId( int $post_id ): bool {
		// 呼び出しが多くなると思われるので、`post_id`で指定された投稿IDがDBに存在するかどうかのチェックは省略する。

		// 自然数(0を含まない正の整数)の場合、trueとする。
		return is_int( $post_id ) && $post_id > 0;
	}


	/**
	 * 有効なチェーンIDかどうかを返します。
	 *
	 * @param int $chain_id チェーンID
	 * @return bool 有効なチェーンIDの場合はtrue
	 */
	public static function isChainId( int $chain_id ): bool {
		// 定義ファイルから、チェーンID一覧が取得可能なものを選定。
		// 今回は、ブロック承認数の既定値を定義している箇所からチェーンID一覧を取得する。

		// チェーンIDは1以上の整数。それ以外はfalseを返す。
		if ( ! is_int( $chain_id ) || $chain_id <= 0 ) {
			return false;
		}

		// キーがチェーンIDなので、キー一覧をint型に変換した配列を取得
		$chain_ids = array_map( 'intval', array_keys( Constants::get( 'default.confirmations' ) ) );

		// 引数のチェーンIDが含まれているかどうかを返す
		return in_array( $chain_id, $chain_ids );
	}

	public static function isNetworkType( string $network_type ): bool {
		switch ( $network_type ) {
			case 'mainnet':
			case 'testnet':
			case 'privatenet':
				return true;
			default:
				return false;
		}
	}

	/**
	 * 引数が、ログレベルを設定する対象の文字列として有効かどうかを返します。
	 *
	 * @param string $log_target
	 * @return bool
	 */
	public static function isLogLevelTarget( string $log_target ): bool {
		// Constantsから定義を取得し、その中に含まれているかどうかを返す。
		/** @var array<string, string> */
		$default_log_levels = Constants::get( 'default.logLevel' );

		// $log_targetがkeyの値として含まれれているかどうかを返す
		return in_array( $log_target, array_keys( $default_log_levels ) );
	}

	public static function isLogLevel( string $log_level ): bool {
		// Constantsから定義を取得し、その中に含まれているかどうかを返す。
		/** @var string[] */
		$log_levels = Constants::get( 'logLevels' );

		// $log_levelが含まれているかどうかを返す
		return in_array( $log_level, $log_levels );
	}
}

/*
memo:
	is_int(23)     => true
	is_int(-23)    => true
	is_int('23')   => false
	is_int(23.5)   => false
	is_int('23.5') => false
	is_int(NULL)   => false
	is_int(true)   => false
	is_int(false)  => false
 */
