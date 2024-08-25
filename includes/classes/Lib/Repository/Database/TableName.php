<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository\Database;

use Cornix\Serendipity\Core\Lib\SystemInfo\PluginInfo;

abstract class TableName {

	// 定数の値(テーブル名)は変更しないでください
	// テーブル作成済みの実環境と不整合が発生し、テストは通るが実環境でエラーが発生する、という状況になります。

	private const POST_SETTING_HISTORY = 'hist_post_setting';

	private static function get( string $table_name ): string {
		$prefix = ( new PluginInfo() )->tableNamePrefix();

		return $prefix . $table_name;
	}

	public static function postSettingHistory(): string {
		return self::get( self::POST_SETTING_HISTORY );
	}
}
