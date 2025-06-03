<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\ValueObject\TableRecord;

use stdClass;

class InvoiceNonceTableRecord extends TableRecordBase {
	public function __construct( stdClass $record ) {
		$this->import( $record );
	}

	protected string $invoice_id;
	protected string $nonce;

	public function invoiceID(): string {
		return $this->invoice_id;
	}

	public function nonce(): string {
		return $this->nonce;
	}
}
