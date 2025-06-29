<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Logging;

use Cornix\Serendipity\Core\Infrastructure\Logging\ValueObject\LogLevel;

interface Logger {
	/**
	 * ログを記録します。
	 *
	 * @param LogLevel          $level
	 * @param string|\Throwable $message_or_exception
	 */
	public function log( LogLevel $level, $message_or_exception ): void;
}
