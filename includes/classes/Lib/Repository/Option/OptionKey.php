<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository\Option;

use Cornix\Serendipity\Core\Lib\SystemInfo\PluginInfo;

final class OptionKey {
	public const DB_SCHEMA_VERSION = 'db_schema_version';

	public static function prefix(): string {
		$prefix = ( new PluginInfo() )->optionNamePrefix();
		assert( ! empty( $prefix ) );
		return $prefix;
	}
}
