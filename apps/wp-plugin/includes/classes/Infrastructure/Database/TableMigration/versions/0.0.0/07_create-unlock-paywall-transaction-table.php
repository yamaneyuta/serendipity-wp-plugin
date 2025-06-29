<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\TableMigration\versions\_0_0_1;

use Cornix\Serendipity\Core\Infrastructure\Database\TableMigration\DatabaseMigrationBase;
use Cornix\Serendipity\Core\Repository\Name\TableName;

return new class() extends DatabaseMigrationBase {

	public function __construct() {
		$this->table_name = ( new TableName() )->unlockPaywallTransaction();
	}
	private string $table_name;

	/** @inheritdoc */
	public function up(): void {
		// - 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		$sql = <<<SQL
			CREATE TABLE `{$this->table_name}` (
				`created_at`          timestamp               NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`invoice_id`          varchar(191)            NOT NULL,
				`chain_id`            bigint        unsigned  NOT NULL,
				`block_number`        bigint        unsigned  NOT NULL,
				`transaction_hash`    varchar(191)            NOT NULL,
				PRIMARY KEY (`invoice_id`),
				KEY `idx_{$this->table_name}_1D00B82F` (`created_at`)
			) {$this->wpdb()->get_charset_collate()};
		SQL;

		$result = $this->mysqli()->query( $sql );
		if ( true !== $result ) {
			throw new \RuntimeException( '[27E12004] Error: ' . $this->mysqli()->error );
		}
	}

	/** @inheritdoc */
	public function down(): void {
		$sql    = "DROP TABLE IF EXISTS `{$this->table_name}`;";
		$result = $this->mysqli()->query( $sql );
		if ( true !== $result ) {
			throw new \RuntimeException( '[D4E69249] Error: ' . $this->mysqli()->error );
		}
	}
};
