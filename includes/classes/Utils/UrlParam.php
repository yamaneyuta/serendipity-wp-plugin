<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Utils;

use Exception;

/**
 * URLパラメータのチェックやパースを行うクラス。
 */
class UrlParam {

	public static function isChainIdsFormat( string $chain_ids_str ): bool {
		// フォーマット: チェーンIDがパイプで区切られた文字列
		$pattern = '/^\d+(\|\d+)*$/';
		// フォーマットに一致しない場合は処理抜け
		if ( ! preg_match( $pattern, $chain_ids_str ) ) {
			return false;
		}

		// パイプで区切られたチェーンIDを配列に変換
		$chain_id_str_array = explode( '|', $chain_ids_str );

		// すべてのチェーンIDが有効な場合、trueを返す
		return array_reduce(
			$chain_id_str_array,
			function ( $carry, $chain_id_str ) {
				return $carry && self::isChainId( $chain_id_str );
			},
			true
		);
	}

	/**
	 * URLパラメータから取得したチェーンID文字列を配列に変換します。
	 *
	 * @param string $chain_ids_str
	 * @return int[]
	 */
	public static function toChainIds( string $chain_ids_str ): array {
		// パイプで区切られたチェーンIDを配列に変換
		$chain_id_str_array = explode( '|', $chain_ids_str );

		// チェーンID文字列を数値型に変換
		return array_map(
			function ( $chain_id_str ) {
				return self::toChainId( $chain_id_str );
			},
			$chain_id_str_array
		);
	}

	/**
	 * URLパラメータから取得したチェーンID文字列が有効なチェーンIDかどうかを返します。
	 *
	 * @param string $chain_id_str
	 * @return bool
	 */
	public static function isChainId( string $chain_id_str ): bool {
		// チェーンIDは1以上の整数
		return preg_match( '/^\d+$/', $chain_id_str ) && TypeValidator::isChainId( (int) $chain_id_str );
	}

	/**
	 * URLパラメータから取得したチェーンID文字列を数値型に変換します。
	 *
	 * @param string $chain_id_str
	 * @return int
	 */
	public static function toChainId( string $chain_id_str ): int {
		return (int) $chain_id_str;
	}
}
