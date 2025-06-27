<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\Repository;

use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\UnlockPaywallTransactionTable;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceID;
use Cornix\Serendipity\Core\Domain\ValueObject\TransactionHash;

class UnlockPaywallTransactionRepository {

	public function __construct( \wpdb $wpdb ) {
		$this->table = new UnlockPaywallTransactionTable( $wpdb );
	}

	private UnlockPaywallTransactionTable $table;

	public function save( InvoiceID $invoice_id, ChainID $chain_id, BlockNumber $block_number, TransactionHash $transaction_hash ): void {
		$this->table->save( $invoice_id, $chain_id, $block_number, $transaction_hash );
	}
}
