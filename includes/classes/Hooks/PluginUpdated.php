<?php

declare(strict_types=1);

namespace Cornix\Serendipity\Core\Hooks;

use Cornix\Serendipity\Core\Database\Database;
use Cornix\Serendipity\Core\Database\Schema;
use Cornix\Serendipity\Core\PluginMainFile;
use Cornix\Serendipity\Core\Tools\DelayInstaller;

/**
 * プラグインが更新(アップグレードまたはインストール)された時の処理を行います。
 */
class PluginUpdated {

	public function __construct() {
		// プラグインがロードされた時のフックを登録。
		add_action( 'plugins_loaded', array( $this, 'add_action_plugins_loaded' ) );
	}

	public function add_action_plugins_loaded(): void {
		// 管理画面以外では何もしない。
		if ( false === is_admin() ) {
			return;
		}

		$current_plugin_version = PluginMainFile::getVersion(); // 現在のバージョンはファイルから取得
		$prev_plugin_version    = Database::getPluginVersion(); // 以前のバージョンはoptionsテーブルから取得

		// 以前に保存されたバージョンと現在のバージョンが異なる場合はアップデートされたと判断。
		//
		// `register_activation_hook`はプラグインを有効化したタイミングであり、アップグレード時は対象外。
		// https://wordpress.stackexchange.com/questions/39813/register-activation-hook-and-updating
		//
		// `upgrader_post_install`や`upgrader_process_complete`は新しいプラグイン側で動作しない。
		// https://ja.wordpress.org/support/topic/wp6-0-1%E3%81%A7upgrader_post_install%E3%83%95%E3%83%83%E3%82%AF%E3%81%8C%E5%91%BC%E3%81%B0%E3%82%8C%E3%81%AA%E3%81%84/
		if ( $prev_plugin_version !== $current_plugin_version ) {
			$this->pluginUpdated( $prev_plugin_version, $current_plugin_version );
		}
	}

	/**
	 * プラグインが更新(アップグレードまたはインストール)された時の処理を行います。
	 */
	private function pluginUpdated( ?string $prev_version, string $current_version ): void {

		// データベースアップグレード
		Schema::upgrade();

		// サードパーティ製ライブラリインストール
		DelayInstaller::execute();

		// // signerが存在しない場合は作成
		// Database::createPrimarySignerIfNotExists();

		// プラグインのバージョンを保存
		Database::setPluginVersion( $current_version );
	}
}
