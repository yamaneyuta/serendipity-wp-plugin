<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\TableMigration\versions\_0_0_1;

use Cornix\Serendipity\Core\Infrastructure\Database\TableMigration\DatabaseMigrationBase;
use Cornix\Serendipity\Core\Infrastructure\Logging\ValueObject\LogCategory;
use Cornix\Serendipity\Core\Infrastructure\Logging\ValueObject\LogLevel;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Logging\LogLevelProviderImpl;
use Cornix\Serendipity\Core\Infrastructure\WordPress\Service\PrefixProvider;

return new class() extends DatabaseMigrationBase {

	public function __construct() {
		$this->log_level_provider = new LogLevelProviderImpl( new PrefixProvider() );
	}
	private LogLevelProviderImpl $log_level_provider;

	/** @inheritdoc */
	public function up(): void {
		$is_debug = defined( 'WP_DEBUG' ) && WP_DEBUG;
		$this->log_level_provider->setLogLevel( LogCategory::app(), $is_debug ? LogLevel::debug() : LogLevel::info() );
		$this->log_level_provider->setLogLevel( LogCategory::audit(), LogLevel::info() );
	}

	/** @inheritdoc */
	public function down(): void {
		$this->log_level_provider->deleteLogLevel( LogCategory::app() );
		$this->log_level_provider->deleteLogLevel( LogCategory::audit() );
	}
};
