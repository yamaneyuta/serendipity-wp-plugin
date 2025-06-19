<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Logging;

use Cornix\Serendipity\Core\Infrastructure\Logging\LogLevelProvider;
use Cornix\Serendipity\Core\Infrastructure\Logging\ValueObject\LogCategory;
use Cornix\Serendipity\Core\Infrastructure\Logging\ValueObject\LogLevel;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\PrefixProvider;

class LogLevelProviderImpl implements LogLevelProvider {
	public function __construct( PrefixProvider $prefix_provider ) {
		$this->prefix_provider = $prefix_provider;
	}
	private PrefixProvider $prefix_provider;

	/** @inheritdoc */
	public function getLogLevel( LogCategory $category ): LogLevel {
		$log_level_name = get_option( $this->getOptionName( $category ), null );
		// 初期化時に設定されているためnullになることは無い
		assert( $log_level_name !== null, "[F6F7CFF9] Log level for category {$category->name()} is not set." );
		return LogLevel::from( $log_level_name );
	}

	/** @inheritdoc */
	public function setLogLevel( LogCategory $category, LogLevel $level ): void {
		update_option( $this->getOptionName( $category ), $level->name() );
	}

	/** 指定されたログカテゴリのログレベルを削除します。 */
	public function deleteLogLevel( LogCategory $category ): void {
		delete_option( $this->getOptionName( $category ) );
	}

	private function getOptionName( LogCategory $category ): string {
		return $this->prefix_provider->getOptionNamePrefix() . 'log_level_' . $category->name();
	}
}
