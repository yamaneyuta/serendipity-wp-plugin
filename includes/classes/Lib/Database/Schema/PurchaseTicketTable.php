<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Database\Schema;

use Cornix\Serendipity\Core\Lib\Database\MySQLiFactory;
use Cornix\Serendipity\Core\Lib\Repository\Database\TableName;


/**
 * 発行した購入用チケットの情報を保存するテーブル
 */
class PurchaseTicketTable {
	public function __construct( \wpdb $wpdb ) {
		$this->wpdb       = $wpdb;
		$this->mysqli     = ( new MySQLiFactory() )->create( $wpdb );
		$this->table_name = ( new TableName() )->purchaseTicket();
	}

	private \wpdb $wpdb;
	private \mysqli $mysqli;
	private string $table_name;

	/**
	 * 購入用チケットテーブルを作成します。
	 */
	public function create(): void {
		$charset = $this->wpdb->get_charset_collate();

		// - 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		$sql = <<<SQL
			CREATE TABLE `{$this->table_name}` (
				`ticket_id`                   varchar(191)        NOT NULL,
				`selling_amount_hex`           varchar(191)        NOT NULL,
				`selling_decimals`             int                 NOT NULL,
				`selling_symbol`               varchar(191)        NOT NULL,
				PRIMARY KEY (`ticket_id`)
			) ${charset};
		SQL;

		$result = $this->mysqli->query( $sql );
		assert( true === $result );
	}

	/**
	 * 購入用チケットテーブルを削除します。
	 */
	public function drop(): void {
		$sql = <<<SQL
			DROP TABLE IF EXISTS `{$this->table_name}`;
		SQL;

		$result = $this->mysqli->query( $sql );
		assert( true === $result );
	}
}
