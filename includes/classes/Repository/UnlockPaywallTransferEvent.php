<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository;

use Cornix\Serendipity\Core\Repository\Name\TableName;
use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\ValueObject\InvoiceID;

class UnlockPaywallTransferEvent {

	public function __construct( \wpdb $wpdb ) {
		$this->wpdb       = $wpdb;
		$this->table_name = ( new TableName() )->unlockPaywallTransferEvent();
	}

	private \wpdb $wpdb;
	private string $table_name;

	public function save( InvoiceID $invoice_id, int $log_index, string $from_address, string $to_address, string $token_address, string $amount_hex, int $transfer_type ): void {
		Validate::checkAddress( $from_address );
		Validate::checkAddress( $to_address );
		Validate::checkAmountHex( $amount_hex );

		// ※ `INSERT IGNORE`を使用している点に注意
		$sql = <<<SQL
			INSERT IGNORE INTO `{$this->table_name}`
			(`invoice_id`, `log_index`, `from_address`, `to_address`, `token_address`, `amount_hex`, `transfer_type`)
			VALUES (%s, %d, %s, %s, %s, %s, %d)
		SQL;

		$sql = $this->wpdb->prepare( $sql, $invoice_id->ulid(), $log_index, $from_address, $to_address, $token_address, $amount_hex, $transfer_type );

		$result = $this->wpdb->query( $sql );
		assert( false !== $result );
	}
}
