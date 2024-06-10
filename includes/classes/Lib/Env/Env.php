<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Env;

use Cornix\Serendipity\Core\Lib\Path\LocalPath;

class Env {
	private function __construct() { }

	/**
	 * @return bool 開発モードの時: true
	 */
	public static function isDevelopmentMode(): bool {
		if ( null === self::$is_development_mode ) {
			// 本プラグインディレクトリ直下にpackage.jsonがある場合、開発モードとして判定する
			self::$is_development_mode = file_exists( LocalPath::get( './package.json' ) );
		}
		return self::$is_development_mode;
	}
	private static $is_development_mode = null;
}
