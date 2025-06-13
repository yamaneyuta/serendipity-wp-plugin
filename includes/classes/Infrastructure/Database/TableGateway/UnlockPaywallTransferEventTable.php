<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\TableGateway;

use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\Repository\Name\TableName;
use Cornix\Serendipity\Core\ValueObject\Address;
use Cornix\Serendipity\Core\ValueObject\InvoiceID;

/**
 * ペイウォール解除イベントのログ
 */
class UnlockPaywallTransferEventTable extends TableBase {
	public function __construct( \wpdb $wpdb ) {
		parent::__construct( $wpdb, ( new TableName() )->unlockPaywallTransferEvent() );
	}

	public function save( InvoiceID $invoice_id, int $log_index, Address $from, Address $to, Address $token_address, string $amount_hex, int $transfer_type ): void {
		Validate::checkAmountHex( $amount_hex );

		$sql = <<<SQL
			INSERT INTO `{$this->tableName()}`
			(`invoice_id`, `log_index`, `from_address`, `to_address`, `token_address`, `amount_hex`, `transfer_type`)
			VALUES (%s, %d, %s, %s, %s, %s, %d)
		SQL;

		$sql = $this->wpdb()->prepare( $sql, $invoice_id->ulid(), $log_index, $from->value(), $to->value(), $token_address->value(), $amount_hex, $transfer_type );

		$result = $this->wpdb()->query( $sql );
		if ( false === $result ) {
			throw new \RuntimeException( '[86C68ECA] Failed to save unlock paywall transfer event. ' . $this->wpdb()->last_error );
		}
	}
}
