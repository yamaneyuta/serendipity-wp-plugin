<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Env;

class Env {
	private function __construct() { }

	/**
	 * @return bool 開発モードの時: true
	 */
	public static function isDevelopmentMode(): bool {
		if ( null === self::$is_development_mode ) {
			// 親フォルダにpackage.jsonがある場合、開発モードとして判定する
			$package_json_path         = __DIR__ . '/../../../package.json';
			self::$is_development_mode = file_exists( $package_json_path );
		}
		return self::$is_development_mode;
	}
	private static $is_development_mode = null;
}
