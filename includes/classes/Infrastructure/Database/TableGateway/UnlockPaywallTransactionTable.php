<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\TableGateway;

use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\Repository\Name\TableName;
use Cornix\Serendipity\Core\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\ValueObject\ChainID;
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

	public function save( InvoiceID $invoice_id, ChainID $chain_id, BlockNumber $block_number, TransactionHash $transaction_hash ): void {
		// ※ 現時点ではreorgの影響を考慮していないため上書き処理は行わない
		$sql = <<<SQL
			INSERT INTO `{$this->tableName()}`
			(`invoice_id`, `chain_id`, `block_number`, `transaction_hash`)
			VALUES (%s, %d, %d, %s)
		SQL;

		$sql = $this->wpdb()->prepare( $sql, $invoice_id->ulid(), $chain_id->value(), $block_number->int(), $transaction_hash->value() );

		$result = $this->wpdb()->query( $sql );
		if ( false === $result ) {
			throw new \RuntimeException( '[CA6349AD] Failed to save unlock paywall transaction. ' . $this->wpdb()->last_error );
		}
	}
}
