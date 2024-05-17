<?php
// 厳格な型検査モード。(php7.0以上)
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Hooks\Screen;

use Cornix\Serendipity\Core\Logger\Logger;
use Cornix\Serendipity\Core\Utils\Constants;
use Cornix\Serendipity\Core\Utils\LocalPath;
use Cornix\Serendipity\Core\Utils\Url;

use function Cornix\Serendipity\Core\add_inline_common_php_var;

// 外部からのアクセス対策。
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Block extends ScreenBase {
	// Admin関連の出力先ディレクトリ。
	const DIST_DIR = 'build/block/';

	public function __construct() {
		// add_action( 'init', array( $this, 'add_action_init' ) );

		// 管理画面で読み込むスクリプトおよびスタイルの登録
		add_action( 'enqueue_block_assets', array( $this, 'add_action_enqueue_block_assets' ) );

		add_action( 'edit_form_advanced', array( $this, 'add_action_edit_form_advanced' ) );
	}

	public function add_action_init(): void {
		/**
		 * Registers the block using the metadata loaded from the `block.json` file.
		 * Behind the scenes, it registers also all assets so they can be enqueued
		 * through the block editor in the corresponding context.
		 *
		 * @see https://developer.wordpress.org/reference/functions/register_block_type/
		 */
		// register_block_type( LocalPath::get( 'build/block' ) );
		// ↑本来はこちらの方法で登録するが、翻訳ファイルのロードがうまくいかないため、以下の『wp_enqueue_script』を使う方法で登録する。
		// ※ languageフォルダ内に『todo-list-ja-create-block-todo-list-editor-script.json』を格納し、
		// 『wp_set_script_translations('create-block-todo-list-editor-script', 'todo-list', LocalPath::get( 'languages/' ) );』とすれば読みこむことは可能。
		// しかし、『languages/』内のファイル名の統一性に欠けるため、この方法は採用しない。
	}

	public function add_action_enqueue_block_assets(): void {
		// `enqueue_block_assets`は、エディタ画面、フロント画面の両方で呼ばれる。
		// ここでは編集画面に限定するため、`! is_admin()`の時は処理抜け。
		if ( ! is_admin() ) {
			return;
		}

		// ブロックエディタで使用するスクリプトを登録するときのハンドル名を取得。
		$handle = Constants::get( 'scriptHandleName.block' );

		// アセットファイルを読み込む。
		$asset_file = include LocalPath::get( self::DIST_DIR . 'index.asset.php' );

		wp_enqueue_script(
			$handle,
			Url::get( self::DIST_DIR . 'index.js' ),
			$asset_file['dependencies'],
			$asset_file['version'],
			true,   // フッターに出力。
		);

		// 翻訳ファイルを読み込む。
		$ret = wp_set_script_translations( $handle, 'todo-list', LocalPath::get( 'languages/' ) );
		if ( true !== $ret ) {
			Logger::error( 'wp_set_script_translations function returned other than true. ret: ' . var_export( $ret, true ) );
			throw new \Exception( '{BD677D16-4F4F-4A1F-855F-F0CDF34A3F25}' );
		}

		// javascriptで使用する変数を出力。
		$this->addInlineCommonPhpVar( $handle );
	}

	public function add_action_edit_form_advanced(): void {
		global $pagenow;
		if ( 'post.php' !== $pagenow && 'post-new.php' !== $pagenow ) {
			// 投稿画面以外では何もしない
			return;
		}
		if ( ! is_user_logged_in() ) {
			throw new \Exception( '{7A45617E-A72F-487F-A4C6-BCEEC864DD99}' );
		}

		// REST API認証用のnonceフィールドを出力。
		wp_nonce_field( 'wp_rest', Constants::get( 'divId.nonceId' ) );
	}
}
