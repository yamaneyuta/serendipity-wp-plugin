<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core;

class PluginMainFile {

	/** @var string */
	private static $plugin_main_file_path;

	public static function initialize( string $plugin_main_file_path ): void {
		if ( is_string( self::$plugin_main_file_path ) ) {
			// 初期化済みの場合はエラー
			throw new \Exception( '{F247E05D-0259-4D20-8ACB-2C168D0E7643}' );
		}

		self::$plugin_main_file_path = $plugin_main_file_path;
	}

	public static function getPath(): string {
		if ( ! is_string( self::$plugin_main_file_path ) ) {
			// 初期化されていない場合はエラー
			throw new \Exception( '{05C2C63A-E592-4C40-B894-54F097450668}' );
		}

		return self::$plugin_main_file_path;
	}

	/**
	 * プラグインのバージョンを取得します。
	 *
	 * @return string プラグインファイルに記載されているバージョン
	 */
	public static function getVersion(): string {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		return get_plugin_data( self::$plugin_main_file_path )['Version'];
	}
}
