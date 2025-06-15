<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Entity;

use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceID;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceNonce;
use Cornix\Serendipity\Core\Domain\ValueObject\Price;

class Invoice {

	public function __construct( InvoiceID $id, int $post_ID, ChainID $chain_ID, Price $selling_price, Address $seller_address, Address $payment_token_address, string $payment_amount_hex, Address $consumer_address, ?InvoiceNonce $nonce = null ) {
		$this->id                    = $id;
		$this->post_ID               = $post_ID;
		$this->chain_ID              = $chain_ID;
		$this->selling_price         = $selling_price;
		$this->seller_address        = $seller_address;
		$this->payment_token_address = $payment_token_address;
		$this->payment_amount_hex    = $payment_amount_hex;
		$this->consumer_address      = $consumer_address;
		$this->nonce                 = $nonce;
	}

	private InvoiceID $id;
	private int $post_ID;
	private ChainID $chain_ID;
	private Price $selling_price;
	private Address $seller_address;
	private Address $payment_token_address;
	private string $payment_amount_hex;
	private Address $consumer_address;
	private ?InvoiceNonce $nonce;

	public function id(): InvoiceID {
		return $this->id;
	}
	public function postID(): int {
		return $this->post_ID;
	}
	public function chainID(): ChainID {
		return $this->chain_ID;
	}
	public function sellingPrice(): Price {
		return $this->selling_price;
	}
	public function sellerAddress(): Address {
		return $this->seller_address;
	}
	public function paymentTokenAddress(): Address {
		return $this->payment_token_address;
	}
	public function paymentAmountHex(): string {
		return $this->payment_amount_hex;
	}
	public function consumerAddress(): Address {
		return $this->consumer_address;
	}
	public function nonce(): ?InvoiceNonce {
		return $this->nonce;
	}
}
