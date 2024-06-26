<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\SystemInfo;

class PluginInfo {

	/**
	 * テーブル名のプレフィックスを取得します。
	 */
	public function tableNamePrefix(): string {
		$company_name = strtolower( $this->companyName() );
		$text_domain  = strtolower( $this->textDomain() );

		return "${company_name}_${text_domain}_";
	}

	/**
	 * オプション名のプレフィックスを取得します。
	 *
	 * ※ `get_option`や`update_option`呼び出し時の第一引数に使用します。
	 */
	public function optionNamePrefix(): string {
		// テーブル名のプレフィックスと同じものを返す
		return $this->tableNamePrefix();
	}

	/**
	 * プラグインのテキストドメインを取得します。
	 */
	public function textDomain(): string {
		return ( new PluginMainFile() )->get( 'TextDomain' );
	}

	/**
	 * 会社名を取得します。
	 */
	private function companyName(): string {
		return 'Cornix';
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
