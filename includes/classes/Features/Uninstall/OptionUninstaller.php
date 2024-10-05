<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\Uninstall;

use Cornix\Serendipity\Core\Lib\Repository\Name\Prefix;

class OptionUninstaller {

	/**
	 * 本プラグインで扱うオプションをすべて削除します。
	 */
	public function execute() {
		$prefix = ( new Prefix() )->optionKeyName();

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
	}
}
