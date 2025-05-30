<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\ValueObject\TableRecord;

use stdClass;

class InvoiceNonceTableRecord {
	public function __construct( stdClass $record ) {
		$this->invoice_id = $record->invoice_id;
		$this->nonce      = $record->nonce;
	}

	public string $invoice_id;
	public string $nonce;
}
