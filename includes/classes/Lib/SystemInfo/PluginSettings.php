<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\SystemInfo;

use Cornix\Serendipity\Core\Types\Price;

/**
 * 本プラグイン用の値を取得するクラス。
 * ※ 本クラスは、サイト管理者等が画面から設定できる値を取得するためのクラスです。
 */
class PluginSettings {

	public function getPostSellingPrice( int $post_ID ): Price {
		// TODO
		return new Price( '0x1853', get_current_user_id(), 'USD' );
	}
}
