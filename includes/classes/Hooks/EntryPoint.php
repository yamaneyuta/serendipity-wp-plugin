<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Hooks;

use Cornix\Serendipity\Core\Hooks\API\GraphQLHook;
use Cornix\Serendipity\Core\Hooks\Page\PostEditHook;
use Cornix\Serendipity\Core\Lib\Rest\RestProperty;

/**
 * フック登録のエントリーポイント
 */
class EntryPoint {
	public function __construct() {

		// GraphQLのAPI登録
		( new GraphQLHook( new RestProperty() ) )->register();

		// 投稿(新規/編集)画面
		( new PostEditHook() )->register();
	}
}
