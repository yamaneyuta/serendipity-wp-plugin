<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Logging;

use Cornix\Serendipity\Core\Infrastructure\Logging\Logger;
use Cornix\Serendipity\Core\Infrastructure\Logging\LogLevelProvider;
use Cornix\Serendipity\Core\Infrastructure\Logging\ValueObject\LogCategory;
use Cornix\Serendipity\Core\Infrastructure\Logging\ValueObject\LogLevel;

class AppLogger {
	public function __construct( Logger $logger, LogLevelProvider $log_level_provider ) {
		$this->logger             = $logger;
		$this->log_level_provider = $log_level_provider;
	}
	private Logger $logger;
	private LogLevelProvider $log_level_provider;

	private function log( LogLevel $level, $message_or_exception ): void {
		try {
			$current_log_level = $this->log_level_provider->getLogLevel( LogCategory::app() );
			if ( $current_log_level->allows( $level ) ) {
				// 現在設定されているログレベルで出力する場合に限り、ログを出力する
				$this->logger->log( $level, $message_or_exception );
			}
		} catch ( \Throwable $e ) {
			// ログの無限ループに陥らないようにerror_logで出力するだけ
			try {
				error_log( (string) $message_or_exception );
				error_log( (string) $e );
			} catch ( \Throwable $e2 ) {
				// Do nothing
			}
		}
	}

	public function debug( $message_or_exception ): void {
		$this->log( LogLevel::debug(), $message_or_exception );
	}
	public function info( $message_or_exception ): void {
		$this->log( LogLevel::info(), $message_or_exception );
	}
	public function warn( $message_or_exception ): void {
		$this->log( LogLevel::warn(), $message_or_exception );
	}
	public function error( $message_or_exception ): void {
		$this->log( LogLevel::error(), $message_or_exception );
	}
}
