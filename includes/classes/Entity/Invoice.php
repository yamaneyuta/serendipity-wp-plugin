<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Entity;

use Cornix\Serendipity\Core\ValueObject\InvoiceID;
use Cornix\Serendipity\Core\ValueObject\InvoiceNonce;
use Cornix\Serendipity\Core\ValueObject\Price;
use Cornix\Serendipity\Core\ValueObject\TableRecord\InvoiceNonceTableRecord;
use Cornix\Serendipity\Core\ValueObject\TableRecord\InvoiceTableRecord;

class Invoice {

	public function __construct( InvoiceID $id, int $post_ID, int $chain_ID, Price $selling_price, string $seller_address, string $payment_token_address, string $payment_amount_hex, string $consumer_address, ?InvoiceNonce $nonce = null ) {
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
	public string $seller_address;
	public string $payment_token_address;
	public string $payment_amount_hex;
	public string $consumer_address;
	public ?InvoiceNonce $nonce;

	public static function fromTableRecord( InvoiceTableRecord $invoice_record, ?InvoiceNonceTableRecord $invoice_nonce_record ): self {
		$nonce = is_null( $invoice_nonce_record ) ? null : new InvoiceNonce( $invoice_nonce_record->nonce );

		return new self(
			InvoiceID::from( $invoice_record->id ),
			$invoice_record->post_id,
			$invoice_record->chain_id,
			new Price(
				$invoice_record->selling_amount_hex,
				$invoice_record->selling_decimals,
				$invoice_record->selling_symbol
			),
			$invoice_record->seller_address,
			$invoice_record->payment_token_address,
			$invoice_record->payment_amount_hex,
			$invoice_record->consumer_address,
			$nonce
		);
	}
}
