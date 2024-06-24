<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Logger;

class Logger {
	public static function debug( $message ) {
		error_log( '[DEBUG] ' . $message );
	}
	public static function info( $message ) {
		error_log( '[INFO] ' . $message );
	}
	public static function warn( $message ) {
		error_log( '[WARN] ' . $message );
	}
	public static function error( $message ) {
		error_log( '[ERROR] ' . $message );
	}
}
