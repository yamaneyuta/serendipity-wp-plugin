<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Hooks\Update;

use Cornix\Serendipity\Core\Features\Update\PluginUpdater;
use Cornix\Serendipity\Core\Lib\Option\OptionFactory;
use Cornix\Serendipity\Core\Repository\PluginInfo;

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
		add_action( 'admin_init', array( $this, 'addActionAdminInit' ) );
	}

	public function addActionAdminInit(): void {
		assert( is_admin() );

		// 方針:
		// optionsテーブルに保存されているプラグインのバージョン(最後にインストールされたバージョン)を取得し、
		// 現在のプラグインのバージョンと比較する。
		// 現在のプラグインバージョンの方が新しい場合は、プラグインのアップデート処理を実行する。
		// プラグインのアップデート処理が完了後、optionsテーブルに保存されているプラグインのバージョンを更新する。

		$option = ( new OptionFactory() )->lastInstalledPluginVersion();
		/** @var string|null */
		$last_ver    = $option->get( null ); // 最後にインストールされたプラグインバージョン
		$current_ver = ( new PluginInfo() )->version(); // 現在のプラグインバージョン

		if ( ! $this->isUpgradeNeeded( $last_ver, $current_ver ) ) {
			// アップグレード不要な場合は処理抜け
			// ※ ダウングレード(非推奨)された場合もここを通ることに注意
			// 　 ダウングレードしても動作する可能性がゼロではないので、エラーとはしていない
			return;
		}

		// 更新処理
		( new PluginUpdater() )->update( $last_ver, $current_ver );

		// プラグインのバージョンを保存
		$option->update( $current_ver );

		// ※ 更新処理が失敗した時、エラーの無限ループに陥る可能性がある。
		// 　 エラー発生時にプラグインを無効化することを検討したが、
		// 　 無効化されたことに気づかない(有料部分が見える状態の)ままになるリスクがあると判断し
		// 　 プラグインの無効化処理は実装していない。
	}

	/** アップグレード処理が必要かどうかを取得します。 */
	private function isUpgradeNeeded( ?string $last_version, string $current_version ): bool {
		// 初回インストール時はアップグレードが必要
		if ( null === $last_version ) {
			return true;
		}

		// バージョンを比較して現在のプラグインバージョンの方が新しい場合、アップグレードが必要
		// ※ ダウングレード(非推奨)された場合はアップグレード不要の判定
		return version_compare( $last_version, $current_version, '<' );
	}
}
