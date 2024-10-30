<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Repository\PluginInfo;

class ExternalApiAccess {
	/**
	 * 外部APIに接続するテストを実施するかどうかの状態を取得します。
	 * 開発環境とCIの1つが実施対象となるように調整しています。
	 */
	public static function isTesting(): bool {
		if ( is_bool( self::$_isTesting ) ) {
			return self::$_isTesting;
		}

		$is_test_php_version = self::versionEqual( phpversion(), ( new PluginInfo() )->requiresPHP() );
		$is_test_wp_version  = self::versionEqual( $GLOBALS['wp_version'], ( new PluginInfo() )->requiresWP() );

		self::$_isTesting = $is_test_php_version && $is_test_wp_version;

		return self::$_isTesting;
	}
	private static ?bool $_isTesting = null;


	/** 簡易的にバージョンが一致するかどうか判定するメソッド */
	private static function versionEqual( string $version1, string $version2 ): bool {
		$major1 = (int) explode( '.', $version1 )[0];
		$minor1 = (int) explode( '.', $version1 )[1];

		$major2 = (int) explode( '.', $version2 )[0];
		$minor2 = (int) explode( '.', $version2 )[1];

		return $major1 == $major2 && $minor1 == $minor2;
	}
}
