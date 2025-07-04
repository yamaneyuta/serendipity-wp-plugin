<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\Repository;

use Cornix\Serendipity\Core\Domain\Entity\Invoice;
use Cornix\Serendipity\Core\Domain\Repository\InvoiceRepository;
use Cornix\Serendipity\Core\Infrastructure\Database\Entity\InvoiceImpl;
use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\InvoiceTable;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceID;

class InvoiceRepositoryImpl implements InvoiceRepository {

	public function __construct( InvoiceTable $invoice_table ) {
		$this->invoice_table = $invoice_table;
	}

	private InvoiceTable $invoice_table;

	/** @inheritdoc */
	public function save( Invoice $invoice ): void {
		// 請求書情報を保存
		$this->invoice_table->insert( $invoice );
	}

	/** @inheritdoc */
	public function get( InvoiceID $invoice_ID ): ?Invoice {
		$invoice_record = $this->invoice_table->select( $invoice_ID );
		return is_null( $invoice_record ) ? null : InvoiceImpl::fromTableRecord( $invoice_record );
	}
}
