<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Logging;

use Cornix\Serendipity\Core\Infrastructure\Logging\ValueObject\LogCategory;
use Cornix\Serendipity\Core\Infrastructure\Logging\ValueObject\LogLevel;

interface LogLevelProvider {
	/** 指定されたログカテゴリの現在のログレベルを取得します。 */
	public function getLogLevel( LogCategory $category ): LogLevel;

	/** 指定されたログカテゴリのログレベルを設定します。 */
	public function setLogLevel( LogCategory $category, LogLevel $level ): void;
}
