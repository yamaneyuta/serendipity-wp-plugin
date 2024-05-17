<?php

// 厳格な型検査モード。(php7.0以上)
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Hooks\Screen;

use Cornix\Serendipity\Core\Env\Env;
use Cornix\Serendipity\Core\Utils\Constants;
use Cornix\Serendipity\Core\Utils\LocalPath;
use Cornix\Serendipity\Core\Utils\Url;

use function Cornix\Serendipity\Core\add_inline_common_php_var;

/**
 * 管理画面関連の処理をまとめたクラス。
 */
class Admin extends ScreenBase {
	// Admin関連の出力先ディレクトリ。
	// Output destination directory for Admin-related output.
	const DIST_DIR = 'public/admin/';

	public function __construct() {
		// 翻訳ファイルを準備しない時は以下のURLを参照。
		// https://ja.wordpress.org/team/handbook/block-editor/how-to-guides/internationalization/

		add_action( 'init', array( $this, 'add_action_init' ) );
		add_filter( 'load_textdomain_mofile', array( $this, 'add_filter_load_textdomain_mofile' ), 10, 2 ); // TODO: 後ろのパラメータの妥当性を確認

		// 管理画面のメニュー追加。
		add_action( 'admin_menu', array( $this, 'add_action_admin_menu' ) );

		// 管理画面で読み込むスクリプトおよびスタイルの登録
		add_action( 'admin_enqueue_scripts', array( $this, 'add_action_admin_enqueue_scripts' ) );
	}

	public function add_action_init(): void {
		if ( false === is_admin() ) {
			return;
		}

		$this->loadPluginTextdomain();
	}

	private function loadPluginTextdomain(): bool {
		// このプラグインディレクトリにある『languages』ディレクトリ以下を『todo-list』ドメイン場所として指定する。
		return load_plugin_textdomain( 'todo-list', false, LocalPath::get( 'languages' ) );
	}

	public function add_filter_load_textdomain_mofile( $mofile, $domain ) {
		if ( false === is_admin() ) {
			return $mofile;
		}

		// 『todo-list』をドメインとして指定された時、プラグインディレクトリ内の『languages』ディレクトリ以下に格納されているファイルを指定する。
		if ( 'todo-list' === $domain && false !== strpos( $mofile, WP_LANG_DIR . '/plugins/' ) ) {
			$locale = apply_filters( 'plugin_locale', determine_locale(), $domain );
			$mofile = LocalPath::get( 'languages/' . $domain . '-' . $locale . '.mo' );
		}

		return $mofile;
	}

	public function add_action_admin_menu(): void {
		if ( false === is_admin() ) {
			return;
		}

		// 仮のスラッグ。メニューに表示しない画面はこのslugを`add_submenu_page`の`$parent_slug`に指定する。(値は重複しなければ良いので適当)
		// -> WordPress6.4あたりから、`add_submenu_page`の`$parent_slug`に`null`を指定するとエラーが発生するようになったため、その対応。
		$fictitious_slug = 'cf84bc80-d2bd-4f3b-834a-6a0f0b875ff4';

		$top_level_menu_slug   = Constants::get( 'slug.root' );
		$settings_menu_slug    = Constants::get( 'slug.settings' ); // 設定画面のURLに使用されるスラッグ
		$development_menu_slug = Constants::get( 'slug.development' ); // 開発者用画面のURLに使用されるスラッグ

		// TODO: 削除
		$report_settings_menu_slug = Constants::get( 'slug.reportSettings' ); // レポート設定画面のURLに使用されるスラッグ
		$sales_history_menu_slug   = Constants::get( 'slug.salesHistory' ); // 販売履歴画面のURLに使用されるスラッグ
		$debug_menu_slug           = Constants::get( 'slug.debug' ); // デバッグ画面のURLに使用されるスラッグ

		$capability = 'manage_options'; // ユーザー権限

		// トップレベルメニュー追加
		$page_title = $menu_title = __( 'Serendipity', 'todo-list' );
		add_menu_page(
			$page_title,   // メニューが表示された際のページのタイトルタグに表示されるテキスト（ブラウザのタブに表示されるテキスト）
			$menu_title,   // 管理画面上のメニュー表示名
			$capability,   // ユーザー権限
			$top_level_menu_slug,   // メニューのスラッグ
			function () {
				// ページは表示されないはずなので例外を投げる。
				throw new \Exception( '{508D89C7-30D6-4E30-833C-5826D8DBAA58}' );
			},
			'dashicons-admin-generic'   // アイコン
		);

		// サブレベルメニュー『設定』追加
		$page_title = $menu_title = __( 'Settings', 'todo-list' );    // 管理画面サブメニュー。
		add_submenu_page(
			$top_level_menu_slug,   // 親メニューのスラッグ名
			$page_title,            // サブメニューが有効化された際にHTMLページタイトルに表示されるテキスト（ブラウザのタブに表示されるテキスト）
			$menu_title,            // サブメニューの管理画面上での名前
			$capability,            // ユーザーがこのメニュー表示する際に必要な権限
			$settings_menu_slug,    // このサブメニューページの一意の識別子
			function () {
				$this->echoAdminSettingsPage( Constants::get( 'divId.settingsPage' ) );
			}
		);

		// サブレベルメニュー『レポート設定』追加
		$page_title = $menu_title = __( 'Report Settings', 'todo-list' );    // 管理画面サブメニュー。
		add_submenu_page(
			$top_level_menu_slug,   // 親メニューのスラッグ名
			$page_title,            // サブメニューが有効化された際にHTMLページタイトルに表示されるテキスト（ブラウザのタブに表示されるテキスト）
			$page_title,            // サブメニューの管理画面上での名前
			$capability,            // ユーザーがこのメニュー表示する際に必要な権限
			$report_settings_menu_slug,    // このサブメニューページの一意の識別子
			function () {
				$this->echoAdminSettingsPage( Constants::get( 'divId.reportSettingsPage' ) );
			}
		);

		// サブレベルメニュー『販売履歴』追加
		$page_title = $menu_title = __( 'Sales History', 'todo-list' );    // 管理画面サブメニュー。
		add_submenu_page(
			$top_level_menu_slug,   // 親メニューのスラッグ名
			$page_title,            // サブメニューが有効化された際にHTMLページタイトルに表示されるテキスト（ブラウザのタブに表示されるテキスト）
			$page_title,            // サブメニューの管理画面上での名前
			$capability,            // ユーザーがこのメニュー表示する際に必要な権限
			$sales_history_menu_slug,    // このサブメニューページの一意の識別子
			function () {
				$this->echoAdminSettingsPage( Constants::get( 'divId.salesHistoryPage' ) );
			}
		);

		// 『デバッグ』ページ追加
		$page_title = $menu_title = __( 'Debug', 'todo-list' );    // デバッグ画面
		add_submenu_page(
			$fictitious_slug,   // 親メニューのスラッグ名(無効なslugを指定することでメニューに表示しない)
			$page_title,            // サブメニューが有効化された際にHTMLページタイトルに表示されるテキスト（ブラウザのタブに表示されるテキスト）
			$page_title,            // サブメニューの管理画面上での名前
			$capability,            // ユーザーがこのメニュー表示する際に必要な権限
			$debug_menu_slug,    // このサブメニューページの一意の識別子
			function () {
				$this->echoAdminSettingsPage( Constants::get( 'divId.debugPage' ) );
			}
		);

		// 『開発者用』ページ追加
		if ( Env::isDevelopmentMode() ) {
			$page_title = $menu_title = __( 'Development', 'todo-list' );    // 開発画面
			add_submenu_page(
				$top_level_menu_slug,   // 親メニューのスラッグ名(無効なslugを指定することでメニューに表示しない)
				$page_title,            // サブメニューが有効化された際にHTMLページタイトルに表示されるテキスト（ブラウザのタブに表示されるテキスト）
				$page_title,            // サブメニューの管理画面上での名前
				$capability,            // ユーザーがこのメニュー表示する際に必要な権限
				$development_menu_slug,    // このサブメニューページの一意の識別子
				function () {
					$this->echoAdminSettingsPage( Constants::get( 'divId.developmentPage' ) );
				}
			);
		}

		// デフォルトで追加される、トップレベルメニューと同じ表示名のサブメニューを削除
		remove_submenu_page( $top_level_menu_slug, $top_level_menu_slug );
	}

	/**
	 * 管理画面用のHTMLを出力します。
	 */
	private function echoAdminSettingsPage( string $div_id ): void {
		// 管理画面用のdivタグを出力。
		echo '<div id="' . esc_attr( $div_id ) . '"></div>';
		$this->echoNonceField();  // 管理画面ではnonceフィールドを出力。
	}

	/**
	 * REST API認証用のnonceフィールドを出力します。
	 *
	 * @see https://developer.wordpress.org/rest-api/using-the-rest-api/authentication/
	 */
	private function echoNonceField(): void {
		if ( ! is_user_logged_in() ) {
			throw new \Exception( '{43CA02E4-26B8-45CB-9B63-EB9770808196}' );
		}
		wp_nonce_field( 'wp_rest', Constants::get( 'divId.nonceId' ) );
	}

	public function add_action_admin_enqueue_scripts( string $hook_suffix ): void {
		if ( false === is_admin() || false === $this->isSettingsPage() ) {
			return;
		}

		// アセットファイルを読み込む。
		$asset_file = include LocalPath::get( self::DIST_DIR . 'index.asset.php' );

		$handle = Constants::get( 'scriptHandleName.admin' );

		// 管理者用のスクリプトを読み込む。
		wp_enqueue_script(
			$handle,
			$this->getFileUrl( 'index.js' ),
			$asset_file['dependencies'],
			$asset_file['version'],
			true,   // フッターに出力。
		);

		// javascriptで使用する変数を出力。
		$this->addInlineCommonPhpVar( $handle );
	}

	// このプラグインの設定画面かどうかを返します。
	private function isSettingsPage(): bool {
		global $pagenow;
		if ( 'admin.php' !== $pagenow ) {  // admin.php: プラグインなどにより拡張したページ
			return false;
		}
		$slugs = Constants::get( 'slug' );

		foreach ( $slugs as $slug ) {
			if ( strlen( $slug ) > 0 && false !== strpos( $_SERVER['REQUEST_URI'], $slug ) ) {
				return true;
			}
		}
		return false;
	}

	// Viewディレクトリ内のファイルに対応するURLを返します。
	private function getFileUrl( string $fileName ): string {
		return Url::get( self::DIST_DIR . $fileName );
	}
}
