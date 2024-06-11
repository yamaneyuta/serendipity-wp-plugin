<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Lib\Plugin;

class Plugin {

	/**
	 * プラグインのテキストドメインを取得します。
	 * 本プラグイン直下のPHPファイルのヘッダコメントから取得したテキストドメインを返します。
	 */
	public static function textDomain(): string {
		self::loadGetPluginDataIfNeeded();
		return get_plugin_data( self::getPluginMainFilePath() )['TextDomain'];
	}

	/**
	 * プラグインバージョンを取得します。
	 * 本プラグイン直下のPHPファイルのヘッダコメントから取得したバージョンを返します。
	 */
	public static function version(): string {
		self::loadGetPluginDataIfNeeded();
		return get_plugin_data( self::getPluginMainFilePath() )['Version'];
	}

	/**
	 * このプラグインが読み込まれるメインファイルのパスを取得します。
	 */
	private static function getPluginMainFilePath(): string {
		$ret = glob( __DIR__ . '/../../../../*.php' );
		assert( count( $ret ) === 1 );
		assert( count( glob( __DIR__ . '/../../../../readme.txt' ) ) === 1 );
		return realpath( $ret[0] );
	}

	/**
	 * get_plugin_data関数を読み込みます。
	 */
	private static function loadGetPluginDataIfNeeded() {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			// @codeCoverageIgnoreStart
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
			// @codeCoverageIgnoreEnd
		}
	}
}
