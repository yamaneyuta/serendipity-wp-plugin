<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository\Option;

use Cornix\Serendipity\Core\Lib\SystemInfo\PluginInfo;

class Option {

	public function __construct() {

		// オプション名に付与するプレフィックスを取得
		$this->prefix = ( new PluginInfo() )->optionNamePrefix();
		assert( strlen( $this->prefix ) > 0 );
	}

	private const KEY_DB_SCHEMA_VERSION = 'db_schema_version';

	/**
	 * オプションに付与するプレフィックス
	 */
	private string $prefix;

	/**
	 * 設定を保存します。
	 */
	private function set( string $option_name, $value, $autoload = null ) {
		$success = update_option( $this->prefix . $option_name, $value, $autoload );
		assert( true === $success );
		return $success;
	}

	/**
	 * 設定を取得します。
	 */
	private function get( string $option_name, $default = false ) {
		return get_option( $this->prefix . $option_name, $default );
	}

	public function uninstall() {
		// `wp_load_alloptions`は`autoload`が`yes`のオプションのみ取得する。
		// `autoload`が`no`のオプションも取得したいので、直接SQLを実行する。

		global $wpdb;
		$query        = <<<SQL
			SELECT `option_name`
			FROM {$wpdb->options}
			WHERE `option_name` LIKE '{$this->prefix}%'
		SQL;
		$option_names = $wpdb->get_col( $query );

		foreach ( $option_names as $option_name ) {
			delete_option( $option_name );
		}
	}

	/**
	 * データベーススキーマバージョンを取得します。
	 */
	public function getDBSchemaVersion( $default = '0.0.0' ): ?string {
		return $this->get( self::KEY_DB_SCHEMA_VERSION, $default );
	}

	/**
	 * データベーススキーマバージョンを設定します。
	 */
	public function setDBSchemaVersion( string $version ) {
		// インストール時やアップデート時など、利用頻度が限定されるため、`autoload`は`false`を指定
		return $this->set( self::KEY_DB_SCHEMA_VERSION, $version, false );
	}
}
