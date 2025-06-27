<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\OptionGateway;

use Cornix\Serendipity\Core\Lib\Option\StringOption;
use Cornix\Serendipity\Core\Repository\Name\OptionName;

/**
 * プラグインバージョンを取得または保存するクラス
 * プラグインがインストールされた時にこの値が書き込まれます。
 */
class PluginVersionOption extends StringOption {
	public function __construct() {
		parent::__construct( ( new OptionName() )->pluginVersion() );
	}
}
