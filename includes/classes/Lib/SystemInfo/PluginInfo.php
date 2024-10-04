<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\SystemInfo;

class PluginInfo {

	/**
	 * プラグインのバージョンを取得します。
	 */
	public function version(): string {
		return ( new PluginMainFile() )->get( 'Version' );
	}

	/**
	 * プラグインのテキストドメインを取得します。
	 */
	public function textDomain(): string {
		return ( new PluginMainFile() )->get( 'TextDomain' );
	}
}



/**
 * 本プラグイン直下のPHPファイルに記載のヘッダコメントから情報を取得するクラス。
 *
 * @internal
 */
class PluginMainFile {
	public function __construct() {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// プラグインの情報を取得してフィールドに保持
		$this->_plugin_data = get_plugin_data( $this->getPluginMainFilePath() );
	}

	/** @var array<string,string> */
	private $_plugin_data;

	public function get( string $property ): string {
		assert( array_key_exists( $property, $this->_plugin_data ) );
		return $this->_plugin_data[ $property ];
	}

	/**
	 * このプラグインが読み込まれるメインファイルのパスを取得します。
	 */
	private function getPluginMainFilePath(): string {
		$plugin_root_dir = '/../../../../';
		$ret             = glob( __DIR__ . $plugin_root_dir . '*.php' );
		assert( count( $ret ) === 1 );
		assert( count( glob( __DIR__ . $plugin_root_dir . 'readme.txt' ) ) === 1 );
		return realpath( $ret[0] );
	}
}
