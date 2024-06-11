<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Hooks;

use Cornix\Serendipity\Core\Hooks\API\GraphQLHook;
use Cornix\Serendipity\Core\Hooks\Page\PostEditHook;

/**
 * フック登録のエントリーポイント
 */
class EntryPoint {
	public function __construct() {
		// GraphQL
		( new GraphQLHook() )->register();

		// 投稿(新規/編集)画面
		( new PostEditHook() )->register();
	}
}
