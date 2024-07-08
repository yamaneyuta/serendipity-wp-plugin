<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Hooks\Update;

use Cornix\Serendipity\Core\Features\Migration\DBSchema;

class PluginUpdateHook {

	public function register(): void {
		add_action( 'plugins_loaded', array( $this, 'addActionPluginLoaded' ) );
	}

	public function addActionPluginLoaded(): void {
		// 管理画面以外は処理抜け
		if ( is_admin() ) {
			return;
		}

		// データベースマイグレーション
		global $wpdb;
		( new DBSchema( $wpdb ) )->migrate();
	}
}
