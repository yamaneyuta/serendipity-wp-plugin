<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Hooks;

use Cornix\Serendipity\Core\Hooks\API\GraphQLHook;
use Cornix\Serendipity\Core\Hooks\Page\AdminPageHook;
use Cornix\Serendipity\Core\Hooks\Cron\CronHook;
use Cornix\Serendipity\Core\Hooks\Post\ContentFilterHook;
use Cornix\Serendipity\Core\Hooks\Page\PostEditHook;
use Cornix\Serendipity\Core\Hooks\Page\ViewPageHook;
use Cornix\Serendipity\Core\Hooks\Update\PluginUpdateHook;
use Cornix\Serendipity\Core\Lib\Rest\RestProperty;

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

		// 投稿内容のフィルタ
		( new ContentFilterHook() )->register();
	}
}
