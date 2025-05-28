<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository;

use Cornix\Serendipity\Core\Config\Config;

class PluginInfo {

	public function __construct() {
		$this->plugin_main_file = PluginMainFile::getInstance();
	}
	private PluginMainFile $plugin_main_file;

	/**
	 * プラグインのメインファイルのパスを取得します。
	 */
	public function mainFilePath(): string {
		return $this->plugin_main_file->path();
	}

	/**
	 * プラグインのバージョンを取得します。
	 */
	public function version(): string {
		return $this->plugin_main_file->getProperty( 'Version' );
	}

	/**
	 * プラグインのテキストドメインを取得します。
	 */
	public function textDomain(): string {
		return $this->plugin_main_file->getProperty( 'TextDomain' );
	}

	/**
	 * プラグインの必要なPHPの最低バージョンを取得します。
	 */
	public function requiresPHP(): string {
		return $this->plugin_main_file->getProperty( 'RequiresPHP' );
	}

	/**
	 * プラグインの必要なWordPressの最低バージョンを取得します。
	 */
	public function requiresWP(): string {
		return $this->plugin_main_file->getProperty( 'RequiresWP' );
	}
}



/**
 * 本プラグイン直下のPHPファイルに記載のヘッダコメントから情報を取得するクラス。
 * IOを減らすためシングルトンで実装。
 *
 * @internal
 */
class PluginMainFile {

	private function __construct() {
		// Do nothing
	}
	public static function getInstance(): PluginMainFile {
		static $instance = null;
		if ( $instance === null ) {
			$instance = new PluginMainFile();
		}
		return $instance;
	}

	// ファイルから取得したプラグインの情報
	/** @var array<string,string>|null */
	private $plugin_data = null;

	// プラグインの最初に読みこまれるファイルのパス
	/** @var string|null */
	private $path = null;

	private function initPluginData(): void {
		assert( $this->plugin_data === null, '[DD2821BD] Plugin data is initialized.' );

		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		// プラグインの情報を取得してフィールドに保持
		//
		// ※ WP6.7.0以降、init()フックより前に`translate`関数を呼び出すとエラーになる。
		// 　 `get_plugin_data`関数内では`_get_plugin_data_markup_translate`が呼び出され、そこから`translate`関数が呼び出される。
		// 　 ここでは、`_get_plugin_data_markup_translate`が呼び出されないように第二引数、第三引数を共にfalseにしている
		// 　 (翻訳済みのプラグインの説明などが必要であれば`translate`関数を呼び出す必要があるが、本プラグイン内の使用範囲では不要)
		// 　 参考: https://github.com/WordPress/wordpress-develop/blob/6.8.1/src/wp-admin/includes/plugin.php#L74-L121
		$this->plugin_data = get_plugin_data( $this->path(), false, false );
	}

	public function getProperty( string $property_name ): string {
		if ( $this->plugin_data === null ) {
			$this->initPluginData();
		}
		assert( $this->plugin_data !== null, '[7F6EA5BA] Plugin data is not initialized.' );

		assert( array_key_exists( $property_name, $this->plugin_data ) );
		return $this->plugin_data[ $property_name ];
	}

	/**
	 * このプラグインが読み込まれるメインファイルのパスを取得します。
	 */
	public function path(): string {
		if ( $this->path === null ) {
			$ret = glob( Config::ROOT_DIR . '/*.php' );
			assert( count( $ret ) === 1 );
			assert( count( glob( Config::ROOT_DIR . '/readme.txt' ) ) === 1 );
			$this->path = realpath( $ret[0] );
		}
		return $this->path;
	}
}
