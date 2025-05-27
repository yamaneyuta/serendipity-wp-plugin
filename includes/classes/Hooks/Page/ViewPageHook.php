<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Hooks\Page;

use Cornix\Serendipity\Core\Features\Page\PhpVer;
use Cornix\Serendipity\Core\Lib\Path\ProjectFile;
use Cornix\Serendipity\Core\Repository\Name\HandleName;

class ViewPageHook {
	public function register(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueueViewScripts' ) );
	}

	public function enqueueViewScripts(): void {
		if ( is_admin() ) {
			return;
		}

		// ゲストユーザー(一般の訪問者)表示用の登録する際のハンドル名を取得
		$handle_name = ( new HandleName() )->viewScript();

		// アセットファイルを読み込む
		$asset_file_path = ( new ProjectFile( 'public/view/index.asset.php' ) )->toLocalPath();
		$asset_file      = include $asset_file_path;

		// スクリプトを登録
		wp_enqueue_script(
			$handle_name,
			( new ProjectFile( 'public/view/index.js' ) )->toUrl(),
			$asset_file['dependencies'],
			$asset_file['version'],
			true   // フッターに出力
		);
		// インラインスクリプトを追加
		( new PhpVer() )->addInlineScript( $handle_name );

		// スタイルを登録
		wp_enqueue_style(
			'5bcfda3bcb3a77e70732c9e6e78195a5', // 適当なハンドル名(他で使用しない)
			( new ProjectFile( 'public/view/index.css' ) )->toUrl(),
			array(),
			$asset_file['version']
		);
	}
}
