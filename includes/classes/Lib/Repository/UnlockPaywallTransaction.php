<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Name\TableName;
use Cornix\Serendipity\Core\Lib\Security\Judge;
use Cornix\Serendipity\Core\Types\InvoiceID;

/**
 * ペイウォール解除時のトランザクションに関するデータを記録するクラス
 */
class UnlockPaywallTransaction {

	public function __construct( \wpdb $wpdb ) {
		$this->wpdb       = $wpdb;
		$this->table_name = ( new TableName() )->unlockPaywallTransaction();
	}

	private \wpdb $wpdb;
	private string $table_name;

	public function save( InvoiceID $invoice_id, int $chain_id, int $block_number, string $transaction_hash ): void {
		Judge::checkBlockNumber( $block_number );
		Judge::checkChainID( $chain_id );
		Judge::checkHex( $transaction_hash );

		// ※ `INSERT IGNORE`を使用している点に注意
		$sql = <<<SQL
			INSERT IGNORE INTO `{$this->table_name}`
			(`invoice_id`, `chain_id`, `block_number`, `transaction_hash`)
			VALUES (%s, %d, %d, %s)
		SQL;

		$sql = $this->wpdb->prepare( $sql, $invoice_id->ulid(), $chain_id, $block_number, $transaction_hash );

		$result = $this->wpdb->query( $sql );
		assert( false !== $result );
	}
}
