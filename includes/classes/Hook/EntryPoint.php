<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Hook;

use Cornix\Serendipity\Core\Hook\Update\PluginUpdateHook;
use Cornix\Serendipity\Core\Lib\Rest\RestProperty;
use Cornix\Serendipity\Core\Presentation\AdminPageHook;
use Cornix\Serendipity\Core\Presentation\ContentIoHook;
use Cornix\Serendipity\Core\Presentation\CronHook;
use Cornix\Serendipity\Core\Presentation\GraphQLHook;
use Cornix\Serendipity\Core\Presentation\PostEditHook;
use Cornix\Serendipity\Core\Presentation\ViewPageHook;

/**
 * フック登録のエントリーポイント
 */
class EntryPoint {
	public function __construct() {

		( new PluginUpdateHook() )->register();

		// Cronの登録
		( new CronHook() )->register();

		// GraphQLのAPI登録
		( new GraphQLHook( new RestProperty() ) )->register();

		// 管理画面
		( new AdminPageHook() )->register();
		// 投稿(新規/編集)画面
		( new PostEditHook() )->register();
		// 投稿表示画面
		( new ViewPageHook() )->register();

		// 投稿を保存または取得する時のフィルタ処理
		( new ContentIoHook() )->register();
	}
}
