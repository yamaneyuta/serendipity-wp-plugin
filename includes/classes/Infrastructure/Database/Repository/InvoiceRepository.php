<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\Repository;

use Cornix\Serendipity\Core\Domain\Entity\Invoice;
use Cornix\Serendipity\Core\Infrastructure\Database\Entity\InvoiceImpl;
use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\InvoiceTable;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceID;

class InvoiceRepository {

	public function __construct( InvoiceTable $invoice_table ) {
		$this->invoice_table = $invoice_table;
	}

	private InvoiceTable $invoice_table;

	public function exists( InvoiceID $invoice_ID ): bool {
		return ! is_null( $this->get( $invoice_ID ) );
	}

	public function add( Invoice $invoice ): void {
		// 請求書情報を保存
		$this->invoice_table->insert( $invoice );
	}

	public function get( InvoiceID $invoice_ID ): ?Invoice {
		$invoice_record = $this->invoice_table->select( $invoice_ID );
		if ( is_null( $invoice_record ) ) {
			return null;
		}

		return InvoiceImpl::fromTableRecord( $invoice_record );
	}
}
