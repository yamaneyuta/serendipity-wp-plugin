<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Entity;

use Cornix\Serendipity\Core\ValueObject\Address;
use Cornix\Serendipity\Core\ValueObject\InvoiceID;
use Cornix\Serendipity\Core\ValueObject\InvoiceNonce;
use Cornix\Serendipity\Core\ValueObject\Price;
use Cornix\Serendipity\Core\ValueObject\TableRecord\InvoiceNonceTableRecord;
use Cornix\Serendipity\Core\ValueObject\TableRecord\InvoiceTableRecord;

class Invoice {

	public function __construct( InvoiceID $id, int $post_ID, int $chain_ID, Price $selling_price, Address $seller_address, Address $payment_token_address, string $payment_amount_hex, Address $consumer_address, ?InvoiceNonce $nonce = null ) {
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

	public InvoiceID $id;
	public int $post_ID;
	public int $chain_ID;
	public Price $selling_price;
	public Address $seller_address;
	public Address $payment_token_address;
	public string $payment_amount_hex;
	public Address $consumer_address;
	public ?InvoiceNonce $nonce;

	public static function fromTableRecord( InvoiceTableRecord $invoice_record, ?InvoiceNonceTableRecord $invoice_nonce_record ): self {
		return new self(
			InvoiceID::from( $invoice_record->id() ),
			$invoice_record->postID(),
			$invoice_record->chainID(),
			new Price(
				$invoice_record->sellingAmountHex(),
				$invoice_record->sellingDecimals(),
				$invoice_record->sellingSymbol()
			),
			Address::from( $invoice_record->sellerAddress() ),
			Address::from( $invoice_record->paymentTokenAddress() ),
			$invoice_record->paymentAmountHex(),
			Address::from( $invoice_record->consumerAddress() ),
			( $invoice_nonce_record ? new InvoiceNonce( $invoice_nonce_record->nonce() ) : null )
		);
	}
}
