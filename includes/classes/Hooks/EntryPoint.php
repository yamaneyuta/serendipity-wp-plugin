<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Hooks;

use Cornix\Serendipity\Core\Features\GraphQL\RootValue;
use Cornix\Serendipity\Core\Hooks\API\GraphQLHook;
use Cornix\Serendipity\Core\Hooks\Page\PostEditHook;
use Cornix\Serendipity\Core\Lib\Rest\RestProperty;
use Cornix\Serendipity\Core\Lib\SystemInfo\PluginSettings;

/**
 * フック登録のエントリーポイント
 */
class EntryPoint {
	public function __construct() {
		$setings = new PluginSettings();

		// GraphQLのAPI登録
		( new GraphQLHook( new RestProperty(), new RootValue( $setings ) ) )->register();

		// 投稿(新規/編集)画面
		( new PostEditHook() )->register();
	}
}
