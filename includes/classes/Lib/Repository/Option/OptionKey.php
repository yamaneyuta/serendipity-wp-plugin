<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository\Option;

use Cornix\Serendipity\Core\Lib\SystemInfo\PluginInfo;

final class OptionKey {

	/** データベーススキーマバージョンを保存するオプションキー */
	public const DB_SCHEMA_VERSION = 'db_schema_version';

	/** プラグインの設定を保存するオプションキーのプレフィックスを取得します。 */
	public static function prefix(): string {
		$prefix = ( new PluginInfo() )->optionNamePrefix();
		assert( ! empty( $prefix ) );
		return $prefix;
	}
}
