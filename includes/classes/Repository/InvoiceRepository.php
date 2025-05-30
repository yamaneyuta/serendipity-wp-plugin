<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository;

use Cornix\Serendipity\Core\Entity\Invoice;
use Cornix\Serendipity\Core\Repository\TableGateway\InvoiceNonceTable;
use Cornix\Serendipity\Core\Repository\TableGateway\InvoiceTable;
use Cornix\Serendipity\Core\ValueObject\InvoiceID;

class InvoiceRepository {

	public function __construct( ?InvoiceTable $invoice_table = null, ?InvoiceNonceTable $invoice_nonce_table = null ) {
		$this->invoice_table       = $invoice_table ?? new InvoiceTable( $GLOBALS['wpdb'] );
		$this->invoice_nonce_table = $invoice_nonce_table ?? new InvoiceNonceTable( $GLOBALS['wpdb'] );
	}

	private InvoiceTable $invoice_table;
	private InvoiceNonceTable $invoice_nonce_table;

	public function exists( InvoiceID $invoice_ID ): bool {
		return (bool) is_null( $this->get( $invoice_ID ) );
	}

	public function add( Invoice $invoice ): void {
		// 請求書情報を保存
		$invoice_ID = $this->invoice_table->insert(
			$invoice->id,
			$invoice->post_ID,
			$invoice->chain_ID,
			$invoice->selling_price,
			$invoice->seller_address,
			$invoice->payment_token_address,
			$invoice->payment_amount_hex,
			$invoice->consumer_address
		);

		// 請求書に紐づくnonceを保存
		if ( ! is_null( $invoice->nonce ) ) {
			$this->invoice_nonce_table->setNonce( $invoice_ID, $invoice->nonce );
		}
	}

	public function get( InvoiceID $invoice_ID ): ?Invoice {
		$invoice_record = $this->invoice_table->select( $invoice_ID );
		if ( is_null( $invoice_record ) ) {
			return null;
		}
		$invoice_nonce_record = $this->invoice_nonce_table->select( $invoice_ID );

		return Invoice::fromTableRecord( $invoice_record, $invoice_nonce_record );
	}
}
