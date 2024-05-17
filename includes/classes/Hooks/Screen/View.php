<?php
// 厳格な型検査モード。(php7.0以上)
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Hooks\Screen;

use Cornix\Serendipity\Core\Utils\Constants;
use Cornix\Serendipity\Core\Utils\LocalPath;
use Cornix\Serendipity\Core\Utils\Url;

use function Cornix\Serendipity\Core\add_inline_common_php_var;

class View extends ScreenBase {
	// View関連の出力先ディレクトリ。
	const DIST_DIR = 'public/view/';

	public function __construct() {
		// ブロック用のアセットを読み込む。
		add_action( 'enqueue_block_assets', array( $this, 'add_action_enqueue_block_assets' ) );

		// javascript側で使用する変数を出力する。
		add_action( 'wp_enqueue_scripts', array( $this, 'add_action_wp_enqueue_scripts' ) );
	}

	// Viewディレクトリ内のファイルに対応するURLを返します。
	private function getFileUrl( string $fileName ): string {
		return Url::get( self::DIST_DIR . $fileName );
	}

	public function add_action_enqueue_block_assets(): void {
		// `enqueue_block_assets`は、エディタ画面、フロント画面の両方で呼ばれる。
		// ここではフロント画面に限定するため、`is_admin()`の時は処理抜け。
		if ( is_admin() ) {
			return;
		}

		// アセットファイルを読み込む。
		$asset_file = include LocalPath::get( self::DIST_DIR . 'index.asset.php' );

		$handle = Constants::get( 'scriptHandleName.view' );

		// ビュー用のスクリプトを読み込む。
		wp_enqueue_script(
			$handle,
			$this->getFileUrl( 'index.js' ),
			$asset_file['dependencies'],
			$asset_file['version'],
			true,   // フッターに出力。
		);

		// //   ビュー用のCSSを読み込む。
		// wp_enqueue_style(
		// 'be6af497-c283-42db-8464-ee8d97bf7d28', //  重複しない適当なID
		// $this->get_file_url( 'index.css' ),
		// array(),
		// $asset_file['version']
		// );

		// javascriptで使用する変数を出力。
		$this->addInlineCommonPhpVar( $handle );
	}

	/**
	 * javascript側で使用する変数を出力します。
	 */
	public function add_action_wp_enqueue_scripts(): void {

		if ( ! is_singular() ) {
			return;
		}

		// REST API認証用のnonceフィールドを出力。
		//
		// 未公開の記事をプレビューする時にnonceが必要となる。
		// プレビュー画面はログインしている状態なので、ログインしている場合のみnonceを出力する。
		if ( is_user_logged_in() ) {
			wp_nonce_field( 'wp_rest', Constants::get( 'divId.nonceId' ) );
		}
	}
}
