<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Hooks\Page;

use Cornix\Serendipity\Core\Features\ExportToJS\RestVer;
use Cornix\Serendipity\Core\Lib\Path\ProjectFile;
use Cornix\Serendipity\Core\Lib\Repository\HandleName;
use Cornix\Serendipity\Core\Lib\Repository\I18n;
use Cornix\Serendipity\Core\Lib\Repository\Slug;

class AdminPageHook {

	public function register(): void {
		// 管理画面のメニュー追加。
		add_action( 'admin_menu', array( $this, 'addActionAdminMenu' ) );

		// 管理画面のスクリプト読み込み。
		add_action( 'admin_enqueue_scripts', array( $this, 'addActionAdminEnqueueScripts' ) );
	}

	public function addActionAdminMenu(): void {
		assert( is_admin() );

		// 仮のスラッグ。メニューに表示しない画面はこのslugを`add_submenu_page`の`$parent_slug`に指定する。(値は重複しなければ良いので適当)
		// -> WordPress6.4あたりから、`add_submenu_page`の`$parent_slug`に`null`を指定するとエラーが発生するようになったため、その対応。
		$fictitious_slug = '9b760f15-09c4-4503-b3cb-5a1bc941dc33';

		$slug = new Slug();
		$i18n = new I18n();

		$capability    = 'manage_options'; // ユーザー権限(`manage_options`は、管理画面の`設定`へアクセス可能な権限)
		$page_callback = function () {
			$div_id = 'b1196eb9-a07c-4c41-9d75-ee6830ce7321';
			echo '<div id="' . esc_attr( $div_id ) . '"></div>';
		};

		// トップレベルメニュー追加
		add_menu_page(
			$i18n->pluginName(),    // メニューが表示された際のページのタイトルタグに表示されるテキスト（ブラウザのタブに表示されるテキスト）
			$i18n->pluginName(),    // 管理画面のメニューに表示されるテキスト
			$capability,            // ユーザー権限
			$slug->adminMenuRoot(), // メニューのスラッグ
			$page_callback,
			'dashicons-admin-generic',  // メニューに表示されるアイコン
		);
	}

	/**
	 * 管理画面で使用するスクリプトを読み込みます
	 */
	public function addActionAdminEnqueueScripts(): void {
		assert( is_admin() );

		// 管理画面用のスクリプトを登録する際のハンドル名を取得
		$handle_name = ( new HandleName() )->adminScript();

		// アセットファイルを読み込む
		$asset_file_path = ( new ProjectFile( 'public/admin/index.asset.php' ) )->toLocalPath();
		$asset_file      = include $asset_file_path;

		// 管理画面のスクリプト読み込み
		wp_enqueue_script(
			$handle_name,
			( new ProjectFile( 'public/admin/index.js' ) )->toUrl(),
			$asset_file['dependencies'],
			$asset_file['version'],
			true,   // フッターに出力。
		);

		// インラインスクリプトを追加
		( new RestVer() )->exportToJS( $handle_name );
	}
}
