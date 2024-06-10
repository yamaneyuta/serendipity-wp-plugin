<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Hooks;

use Cornix\Serendipity\Core\Hooks\Page\PostEditHook;

/**
 * フック登録のエントリーポイント
 */
class EntryPoint {
	public function __construct() {
		// 投稿(新規/編集)画面
		( new PostEditHook() )->register();
	}
}
