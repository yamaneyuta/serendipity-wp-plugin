<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\TableGateway;

use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\Repository\Name\TableName;
use Cornix\Serendipity\Core\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\ValueObject\InvoiceID;
use Cornix\Serendipity\Core\ValueObject\TransactionHash;

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


	public function save( InvoiceID $invoice_id, int $chain_id, BlockNumber $block_number, TransactionHash $transaction_hash ): void {
		Validate::checkChainID( $chain_id );

		// ※ 現時点ではreorgの影響を考慮していないため上書き処理は行わない
		$sql = <<<SQL
			INSERT INTO `{$this->tableName()}`
			(`invoice_id`, `chain_id`, `block_number`, `transaction_hash`)
			VALUES (%s, %d, %d, %s)
		SQL;

		$sql = $this->wpdb()->prepare( $sql, $invoice_id->ulid(), $chain_id, $block_number->int(), $transaction_hash->value() );

		$result = $this->wpdb()->query( $sql );
		if ( false === $result ) {
			throw new \RuntimeException( '[CA6349AD] Failed to save unlock paywall transaction. ' . $this->wpdb()->last_error );
		}
	}
}
