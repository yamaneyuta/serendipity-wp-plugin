<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository\Option;

use Cornix\Serendipity\Core\Lib\SystemInfo\PluginInfo;

class Option {

	public function __construct() {

		// オプション名に付与するプレフィックスを取得
		$this->prefix = ( new PluginInfo() )->optionNamePrefix();
	}

	private const DB_SCHEMA_VERSION = 'db_schema_version';

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
		$all_options = wp_load_alloptions();

		// $all_options のキーがプレフィックスで始まるものを削除
		assert( strlen( $this->prefix ) > 0 );
		foreach ( array_keys( $all_options ) as $option_name ) {
			if ( 0 === strpos( $option_name, $this->prefix ) ) {
				delete_option( $option_name );
			}
		}
	}

	/**
	 * データベーススキーマバージョンを取得します。
	 */
	public function getDBSchemaVersion( $default = '0.0.0' ): ?string {
		return $this->get( self::DB_SCHEMA_VERSION, $default );
	}

	/**
	 * データベーススキーマバージョンを設定します。
	 */
	public function setDBSchemaVersion( string $version ) {
		return $this->set( self::DB_SCHEMA_VERSION, $version );
	}
}
