<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\Uninstall;

use Cornix\Serendipity\Core\Repository\Name\Prefix;

class OptionUninstaller {

	/**
	 * 本プラグインで扱うオプションをすべて削除します。
	 */
	public function execute() {
		$prefix = ( new Prefix() )->optionKeyPrefix();

		// `wp_load_alloptions`は`autoload`が`yes`のオプションのみ取得する。
		// `autoload`が`no`のオプションも取得したいので、直接SQLを実行する。
		global $wpdb;
		$query        = <<<SQL
			SELECT `option_name`
			FROM {$wpdb->options}
			WHERE `option_name` LIKE '{$prefix}%'
		SQL;
		$option_names = $wpdb->get_col( $query );

		foreach ( $option_names as $option_name ) {
			delete_option( $option_name );
		}

		// `transient`も削除する
		$query           = <<<SQL
			SELECT `option_name`
			FROM {$wpdb->options}
			WHERE `option_name` LIKE '_transient_{$prefix}%'
		SQL;
		$transient_names = $wpdb->get_col( $query );
		foreach ( $transient_names as $transient_name ) {
			delete_transient( str_replace( '_transient_', '', $transient_name ) );
		}
	}
}
