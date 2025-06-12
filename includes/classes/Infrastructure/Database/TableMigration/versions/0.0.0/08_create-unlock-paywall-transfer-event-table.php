<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\TableMigration\versions\_0_0_1;

use Cornix\Serendipity\Core\Infrastructure\Database\TableMigration\DatabaseMigrationBase;
use Cornix\Serendipity\Core\Repository\Name\TableName;

return new class() extends DatabaseMigrationBase {

	public function __construct() {
		$this->table_name = ( new TableName() )->unlockPaywallTransferEvent();
	}
	private string $table_name;

	/** @inheritdoc */
	public function up(): void {
		// - 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		$sql = <<<SQL
			CREATE TABLE `{$this->table_name}` (
				`created_at`     timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`invoice_id`     varchar(191)  NOT NULL,
				`log_index`      int           NOT NULL,
				`from_address`   varchar(191)  NOT NULL,
				`to_address`     varchar(191)  NOT NULL,
				`token_address`  varchar(191)  NOT NULL,
				`amount_hex`     varchar(191)  NOT NULL,
				`transfer_type`  int           NOT NULL,
				PRIMARY KEY (`invoice_id`, `log_index`),
				KEY `idx_{$this->table_name}_E1160E22` (`created_at`)
			) {$this->wpdb()->get_charset_collate()};
		SQL;

		$result = $this->mysqli()->query( $sql );
		if ( true !== $result ) {
			throw new \RuntimeException( '[C17A5E96] Error: ' . $this->mysqli()->error );
		}
	}

	/** @inheritdoc */
	public function down(): void {
		$sql    = "DROP TABLE IF EXISTS `{$this->table_name}`;";
		$result = $this->mysqli()->query( $sql );
		if ( true !== $result ) {
			throw new \RuntimeException( '[C6FA9990] Error: ' . $this->mysqli()->error );
		}
	}
};
