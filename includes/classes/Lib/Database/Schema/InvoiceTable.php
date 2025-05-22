<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Database\Schema;

use Cornix\Serendipity\Core\Lib\Database\MySQLiFactory;
use Cornix\Serendipity\Core\Lib\Repository\Name\TableName;


/**
 * 発行した請求書の情報を保存するテーブル
 */
class InvoiceTable {
	public function __construct( \wpdb $wpdb ) {
		$this->wpdb       = $wpdb;
		$this->mysqli     = ( new MySQLiFactory() )->create( $wpdb );
		$this->table_name = ( new TableName() )->invoice();
	}

	private \wpdb $wpdb;
	private \mysqli $mysqli;
	private string $table_name;

	/**
	 * 購入用請求書テーブルを作成します。
	 */
	public function create(): void {
		$charset    = $this->wpdb->get_charset_collate();
		$index_name = "idx_{$this->table_name}_2D6F4376";

		// - 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		$sql = <<<SQL
			CREATE TABLE `{$this->table_name}` (
				`created_at`             timestamp               NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`id`                     varchar(191)            NOT NULL,
				`post_id`			     bigint        unsigned  NOT NULL,
				`chain_id`               bigint        unsigned  NOT NULL,
				`selling_amount_hex`     varchar(191)            NOT NULL,
				`selling_decimals`       int                     NOT NULL,
				`selling_symbol`         varchar(191)            NOT NULL,
				`seller_address`         varchar(191)            NOT NULL,
				`payment_token_address`  varchar(191)            NOT NULL,
				`payment_amount_hex`     varchar(191)            NOT NULL,
				`consumer_address`       varchar(191)            NOT NULL,
				PRIMARY KEY (`id`),
				KEY `{$index_name}` (`created_at`)
			) {$charset};
		SQL;

		$result = $this->mysqli->query( $sql );
		assert( true === $result );
	}

	/**
	 * 購入用請求書テーブルを削除します。
	 */
	public function drop(): void {
		$sql = <<<SQL
			DROP TABLE IF EXISTS `{$this->table_name}`;
		SQL;

		$result = $this->mysqli->query( $sql );
		assert( true === $result );
	}
}
