<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceID;
use Cornix\Serendipity\Core\Domain\ValueObject\TransactionHash;
use Cornix\Serendipity\Core\Domain\ValueObject\UnlockPaywallTransferType;

class UnlockPaywallTransferEvent {
	public function __construct(
		BlockNumber $block_number,
		int $log_index,
		TransactionHash $transaction_hash,
		InvoiceID $invoice_id,
		Address $server_signer_address,
		Address $from_address,
		Address $to_address,
		Address $token_address,
		string $amount_hex,
		UnlockPaywallTransferType $transfer_type
	) {
		$this->block_number          = $block_number;
		$this->log_index             = $log_index;
		$this->transaction_hash      = $transaction_hash;
		$this->invoice_id            = $invoice_id;
		$this->server_signer_address = $server_signer_address;
		$this->from_address          = $from_address;
		$this->to_address            = $to_address;
		$this->token_address         = $token_address;
		$this->amount_hex            = $amount_hex;
		$this->transfer_type         = $transfer_type;
	}
	private BlockNumber $block_number;
	private int $log_index;
	private TransactionHash $transaction_hash;
	private InvoiceID $invoice_id;
	private Address $server_signer_address;
	private Address $from_address;
	private Address $to_address;
	private Address $token_address;
	private string $amount_hex;
	private UnlockPaywallTransferType $transfer_type;

	public function blockNumber(): BlockNumber {
		return $this->block_number;
	}
	public function logIndex(): int {
		return $this->log_index;
	}
	public function transactionHash(): TransactionHash {
		return $this->transaction_hash;
	}
	public function invoiceId(): InvoiceID {
		return $this->invoice_id;
	}
	public function serverSignerAddress(): Address {
		return $this->server_signer_address;
	}
	public function fromAddress(): Address {
		return $this->from_address;
	}
	public function toAddress(): Address {
		return $this->to_address;
	}
	public function tokenAddress(): Address {
		return $this->token_address;
	}
	public function amountHex(): string {
		return $this->amount_hex;
	}
	public function transferType(): UnlockPaywallTransferType {
		return $this->transfer_type;
	}
}
