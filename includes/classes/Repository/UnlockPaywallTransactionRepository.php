<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository;

use Cornix\Serendipity\Core\Repository\TableGateway\UnlockPaywallTransactionTable;
use Cornix\Serendipity\Core\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\ValueObject\InvoiceID;

class UnlockPaywallTransactionRepository {

	public function __construct( \wpdb $wpdb ) {
		$this->table = new UnlockPaywallTransactionTable( $wpdb );
	}

	private UnlockPaywallTransactionTable $table;

	public function save( InvoiceID $invoice_id, int $chain_id, BlockNumber $block_number, string $transaction_hash ): void {
		$this->table->save( $invoice_id, $chain_id, $block_number, $transaction_hash );
	}
}
