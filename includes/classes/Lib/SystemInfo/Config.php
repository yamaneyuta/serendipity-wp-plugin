<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\SystemInfo;

/**
 * 本プラグインで使用する構成情報等を取得するクラス。
 *
 * 画面(ブラウザ)操作によってユーザーが値を書き換えられる場合は、`Settings`クラスを使用してください。
 * インストールされる環境によって取得される値が変わる場合は、`Environment`クラスを使用してください。
 * 例えば、jsonファイルから取得するような場合は、この`Config`クラスを使用します。
 */
class Config {
	public function getPluginInfo( string $property ): string {
		return ( new PluginInfo() )->get( $property );
	}

	public function getHandleName( string $name ): string {
		return ( new HandleName() )->get( $name );
	}
}


/**
 * WordPressのhookに登録する際に使用するハンドル名を取得するクラス。
 *
 * @internal
 */
class HandleName {
	private $_handle_names = array(
		// 『src/block/index.js』(文字列)のMD5ハッシュ値。
		'block_script' => '6e7ba80738b3f81da8c4f83d13e6a344',
	);

	public function get( string $name ): string {
		assert( array_key_exists( $name, $this->_handle_names ) );
		return $this->_handle_names[ $name ];
	}
}


/**
 * 本プラグイン直下のPHPファイルに記載のヘッダコメントから情報を取得するクラス。
 *
 * @internal
 */
class PluginInfo {
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
