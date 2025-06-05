<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository;

use Cornix\Serendipity\Core\Repository\TableGateway\UnlockPaywallTransferEventTable;
use Cornix\Serendipity\Core\ValueObject\InvoiceID;

class UnlockPaywallTransferEventRepository {

	public function __construct( \wpdb $wpdb ) {
		$this->table = new UnlockPaywallTransferEventTable( $wpdb );
	}
	private UnlockPaywallTransferEventTable $table;

	public function save( InvoiceID $invoice_id, int $log_index, string $from_address, string $to_address, string $token_address, string $amount_hex, int $transfer_type ): void {
		$this->table->save( $invoice_id, $log_index, $from_address, $to_address, $token_address, $amount_hex, $transfer_type );
	}
}
