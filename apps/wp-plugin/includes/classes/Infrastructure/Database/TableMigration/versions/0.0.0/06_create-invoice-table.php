<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\TableMigration\versions\_0_0_1;

use Cornix\Serendipity\Core\Infrastructure\Database\TableMigration\DatabaseMigrationBase;
use Cornix\Serendipity\Core\Repository\Name\TableName;

return new class() extends DatabaseMigrationBase {

	public function __construct() {
		$this->table_name = ( new TableName() )->invoice();
	}
	private string $table_name;

	/** @inheritdoc */
	public function up(): void {
		$charset    = $this->wpdb()->get_charset_collate();
		$index_name = "idx_{$this->table_name}_2D6F4376";

		// - 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		$sql = <<<SQL
			CREATE TABLE `{$this->table_name}` (
				`created_at`             timestamp               NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`updated_at`             timestamp               NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`id`                     varchar(191)            NOT NULL,
				`post_id`			     bigint        unsigned  NOT NULL,
				`chain_id`               bigint        unsigned  NOT NULL,
				`selling_amount`         varchar(191)            NOT NULL,
				`selling_symbol`         varchar(191)            NOT NULL,
				`seller_address`         varchar(191)            NOT NULL,
				`payment_token_address`  varchar(191)            NOT NULL,
				`payment_amount`         varchar(191)            NOT NULL,
				`consumer_address`       varchar(191)            NOT NULL,
				`nonce`                  varchar(191) 		     DEFAULT NULL,
				PRIMARY KEY (`id`),
				KEY `{$index_name}` (`created_at`)
			) {$charset};
		SQL;

		$result = $this->mysqli()->query( $sql );
		if ( true !== $result ) {
			throw new \RuntimeException( '[B2257E09] Error: ' . $this->mysqli()->error );
		}
	}

	/** @inheritdoc */
	public function down(): void {
		$sql    = "DROP TABLE IF EXISTS `{$this->table_name}`;";
		$result = $this->mysqli()->query( $sql );
		if ( true !== $result ) {
			throw new \RuntimeException( '[AC75825A] Error: ' . $this->mysqli()->error );
		}
	}
};
