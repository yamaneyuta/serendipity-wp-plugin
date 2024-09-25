<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Hooks\Update;

use Cornix\Serendipity\Core\Features\Migration\DBSchema;

// ■プラグインがインストールされた時や更新時のhookに関して
// - `update_plugins_{$host_name}`
// 　-> WP5.8.0以降で使用可能。2024/9/25時点でWP5.4で開発しているため使用しない
// 　   https://wordpress.stackexchange.com/a/419585
// - `plugins_loaded`, `init`
// 　-> FTPやSVNでプラグインを更新した場合でも検知できるが、フロントエンドを含む全てのページで実行される欠点あり
// - `register_activation_hook`
// 　- ユーザーがプラグインをアクティブにした時のみ実行され、プラグインアップグレード後には呼び出されない旨の情報あり(2012年時点の情報)
// 　  以下のURLでは`register_activation_hook`で現在のバージョンを`wp_options`に保存し、管理ページ読み込み時に都度バージョンを比較することを推奨している
// 　  https://wordpress.stackexchange.com/a/39828
// 　- マルチサイト環境の場合は`admin_init`を使用した方が良い(2011年時点の情報)
// 　  https://core.trac.wordpress.org/ticket/14170#comment:68
// ■プラグインアップグレード前のhookに関して
// - `upgrader_pre_install`を使用(`upgrader_process_complete`は使用しない)
// 　https://stackoverflow.com/a/56179550
// ■その他注意事項
// - マルチサイトの場合、他のサイトに対しても処理が実行されるかどうか確認する必要あり(もしくはサイトIDに依存しない設計にする)

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
