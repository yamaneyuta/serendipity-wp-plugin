<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\Repository;

use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\UnlockPaywallTransferEventTable;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\Amount;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceID;

class UnlockPaywallTransferEventRepository {

	public function __construct( \wpdb $wpdb ) {
		$this->table = new UnlockPaywallTransferEventTable( $wpdb );
	}
	private UnlockPaywallTransferEventTable $table;

	public function save( InvoiceID $invoice_id, int $log_index, Address $from, Address $to, Address $token_address, Amount $amount, int $transfer_type ): void {
		$this->table->save( $invoice_id, $log_index, $from, $to, $token_address, $amount, $transfer_type );
	}
}
