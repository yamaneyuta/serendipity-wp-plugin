<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Name\TableName;
use Cornix\Serendipity\Core\Lib\Security\Judge;
use Cornix\Serendipity\Core\Types\InvoiceIdType;

class UnlockPaywallTransferEvent {

	public function __construct( \wpdb $wpdb ) {
		$this->wpdb       = $wpdb;
		$this->table_name = ( new TableName() )->unlockPaywallTransferEvent();
	}

	private \wpdb $wpdb;
	private string $table_name;

	public function save( InvoiceIdType $invoice_id, int $log_index, string $from_address, string $to_address, string $amount_hex ): void {
		Judge::checkAddress( $from_address );
		Judge::checkAddress( $to_address );
		Judge::checkAmountHex( $amount_hex );

		// ※ `INSERT IGNORE`を使用している点に注意
		$sql = <<<SQL
			INSERT IGNORE INTO `{$this->table_name}`
			(`invoice_id`, `log_index`, `from_address`, `to_address`, `amount_hex`)
			VALUES (%s, %d, %s, %s, %s)
		SQL;

		$sql = $this->wpdb->prepare( $sql, $invoice_id->ulid(), $log_index, $from_address, $to_address, $amount_hex );

		$result = $this->wpdb->query( $sql );
		assert( false !== $result );
	}
}
