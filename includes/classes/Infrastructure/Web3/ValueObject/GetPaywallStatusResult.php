<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceID;

class GetPaywallStatusResult {
	public function __construct( bool $is_unlocked, InvoiceID $invoice_ID, BlockNumber $unlocked_block_number ) {
		$this->is_unlocked           = $is_unlocked;
		$this->invoice_ID            = $invoice_ID;
		$this->unlocked_block_number = $unlocked_block_number;
	}
	private bool $is_unlocked;
	private InvoiceID $invoice_ID;
	private BlockNumber $unlocked_block_number;

	/** ペイウォールが解除済みかどうかを取得します。 */
	public function isUnlocked(): bool {
		return $this->is_unlocked;
	}

	/** ペイウォールを解除した時の請求書IDを取得します。 */
	public function invoiceID(): InvoiceID {
		return $this->invoice_ID;
	}

	/** ペイウォールを解除した時のブロック番号を取得します。 */
	public function unlockedBlockNumber(): BlockNumber {
		return $this->unlocked_block_number;
	}
}
