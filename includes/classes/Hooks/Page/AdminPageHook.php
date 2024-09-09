<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Hooks\Page;

use Cornix\Serendipity\Core\Lib\Repository\I18n;
use Cornix\Serendipity\Core\Lib\Repository\Slug;

class AdminPageHook {

	public function register(): void {
		// 管理画面のメニュー追加。
		add_action( 'admin_menu', array( $this, 'addActionAdminMenu' ) );
	}

	public function addActionAdminMenu(): void {
		assert( is_admin() );

		// 仮のスラッグ。メニューに表示しない画面はこのslugを`add_submenu_page`の`$parent_slug`に指定する。(値は重複しなければ良いので適当)
		// -> WordPress6.4あたりから、`add_submenu_page`の`$parent_slug`に`null`を指定するとエラーが発生するようになったため、その対応。
		$fictitious_slug = '9b760f15-09c4-4503-b3cb-5a1bc941dc33';

		$slug = new Slug();
		$i18n = new I18n();

		$capability = 'manage_options'; // ユーザー権限(`manage_options`は、管理画面の`設定`へアクセス可能な権限)
		$page_callback = function() {
			$div_id = 'b1196eb9-a07c-4c41-9d75-ee6830ce7321';
			echo '<div id="' . esc_attr( $div_id ) . '"></div>';
		};

		// トップレベルメニュー追加
		add_menu_page(
			$i18n->pluginName(),    // メニューが表示された際のページのタイトルタグに表示されるテキスト（ブラウザのタブに表示されるテキスト）
			$i18n->pluginName(),    // 管理画面のメニューに表示されるテキスト
			$capability,            // ユーザー権限
			$slug->adminMenuRoot(), // メニューのスラッグ
			function () {
				// このページは表示されない
				throw new \LogicException( '[B12213EC] This page should not be displayed.' );
			},
			'dashicons-admin-generic',  // メニューに表示されるアイコン
		);

		// サブレベルメニュー『ライセンス』追加
		add_submenu_page(
			$slug->adminMenuRoot(), // 親メニューのスラッグ
			$i18n->adminMenuTitleLicense(), // メニューが表示された際のページのタイトルタグに表示されるテキスト（ブラウザのタブに表示されるテキスト）
			$i18n->adminMenuTitleLicense(), // 管理画面のメニューに表示されるテキスト
			$capability,            // ユーザー権限
			$slug->adminMenuLicense(),  // メニューのスラッグ
			$page_callback,
		);

		// デフォルトで追加される、トップレベルメニューと同じ表示名のサブメニューを削除
		remove_submenu_page( $slug->adminMenuRoot(), $slug->adminMenuRoot() );
	}
}
