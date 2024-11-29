<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

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
		$this->plugin_data = get_plugin_data( $this->path() );
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
			$plugin_root_dir = '/../../../../';
			$ret             = glob( __DIR__ . $plugin_root_dir . '*.php' );
			assert( count( $ret ) === 1 );
			assert( count( glob( __DIR__ . $plugin_root_dir . 'readme.txt' ) ) === 1 );
			$this->path = realpath( $ret[0] );
		}
		return $this->path;
	}
}
