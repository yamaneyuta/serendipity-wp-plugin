<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository\TableGateway;

use Cornix\Serendipity\Core\Repository\Name\TableName;


/**
 * ペイウォール解除時のトランザクションに関するデータを記録するテーブル
 * ※ トランザクションハッシュやブロック番号などの情報を保持
 */
class UnlockPaywallTransactionTable extends TableBase {
	public function __construct( \wpdb $wpdb ) {
		parent::__construct( $wpdb, ( new TableName() )->unlockPaywallTransaction() );
	}

	/**
	 * テーブルを作成します。
	 */
	public function create(): void {
		$charset    = $this->wpdb()->get_charset_collate();
		$index_name = "idx_{$this->tableName()}_1D00B82F";

		// - 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		$sql = <<<SQL
			CREATE TABLE `{$this->tableName()}` (
				`created_at`          timestamp               NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`invoice_id`          varchar(191)            NOT NULL,
				`chain_id`            bigint        unsigned  NOT NULL,
				`block_number`        bigint        unsigned  NOT NULL,
				`transaction_hash`    varchar(191)            NOT NULL,
				PRIMARY KEY (`invoice_id`),
				KEY `{$index_name}` (`created_at`)
			) {$charset};
		SQL;

		$result = $this->mysqli()->query( $sql );
		if ( true !== $result ) {
			throw new \RuntimeException( '[36AD2361] Failed to create unlock paywall transaction table. ' . $this->mysqli()->error );
		}
	}
}
