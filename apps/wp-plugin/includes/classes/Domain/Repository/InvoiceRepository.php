<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Repository;

use Cornix\Serendipity\Core\Domain\Entity\Invoice;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceID;

interface InvoiceRepository {
	/** 指定した請求書IDに合致する請求書情報を取得します。 */
	public function get( InvoiceID $invoice_ID ): ?Invoice;

	/** 請求書情報を保存します。 */
	public function save( Invoice $invoice ): void;
}
