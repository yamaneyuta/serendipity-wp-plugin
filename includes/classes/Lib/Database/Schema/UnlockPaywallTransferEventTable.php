<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Database\Schema;

use Cornix\Serendipity\Core\Lib\Database\MySQLiFactory;
use Cornix\Serendipity\Core\Lib\Repository\Name\TableName;


/**
 * ペイウォール解除イベントのログ
 */
class UnlockPaywallTransferEventTable {
	public function __construct( \wpdb $wpdb ) {
		$this->wpdb       = $wpdb;
		$this->mysqli     = ( new MySQLiFactory() )->create( $wpdb );
		$this->table_name = ( new TableName() )->unlockPaywallTransferEvent();
	}

	private \wpdb $wpdb;
	private \mysqli $mysqli;
	private string $table_name;

	/**
	 * テーブルを作成します。
	 */
	public function create(): void {
		$charset    = $this->wpdb->get_charset_collate();
		$index_name = "idx_{$this->table_name}_E1160E22";

		// - 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		$sql = <<<SQL
			CREATE TABLE `{$this->table_name}` (
				`created_at`     timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`invoice_id`     varchar(191)  NOT NULL,
				`log_index`      int(11)       NOT NULL,
				`from_address`   varchar(191)  NOT NULL,
				`to_address`     varchar(191)  NOT NULL,
				`token_address`  varchar(191)  NOT NULL,
				`amount_hex`     varchar(191)  NOT NULL,
				`transfer_type`  int(11)       NOT NULL,
				PRIMARY KEY (`invoice_id`, `log_index`),
				KEY `{$index_name}` (`created_at`)
			) ${charset};
		SQL;

		$result = $this->mysqli->query( $sql );
		assert( true === $result );
	}

	/**
	 * テーブルを削除します。
	 */
	public function drop(): void {
		$sql = <<<SQL
			DROP TABLE IF EXISTS `{$this->table_name}`;
		SQL;

		$result = $this->mysqli->query( $sql );
		assert( true === $result );
	}
}
