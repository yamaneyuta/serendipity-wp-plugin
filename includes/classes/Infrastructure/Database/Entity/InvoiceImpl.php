<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\Entity;

use Cornix\Serendipity\Core\Domain\Entity\Invoice;
use Cornix\Serendipity\Core\Infrastructure\Database\ValueObject\InvoiceTableRecord;
use Cornix\Serendipity\Core\ValueObject\Address;
use Cornix\Serendipity\Core\ValueObject\ChainID;
use Cornix\Serendipity\Core\ValueObject\InvoiceID;
use Cornix\Serendipity\Core\ValueObject\InvoiceNonce;
use Cornix\Serendipity\Core\ValueObject\Price;

class InvoiceImpl extends Invoice {

	public function __construct( InvoiceID $id, int $post_ID, ChainID $chain_ID, Price $selling_price, Address $seller_address, Address $payment_token_address, string $payment_amount_hex, Address $consumer_address, ?InvoiceNonce $nonce = null ) {
		parent::__construct(
			$id,
			$post_ID,
			$chain_ID,
			$selling_price,
			$seller_address,
			$payment_token_address,
			$payment_amount_hex,
			$consumer_address,
			$nonce
		);
	}

	public static function fromTableRecord( InvoiceTableRecord $invoice_record ): self {
		return new self(
			InvoiceID::from( $invoice_record->idValue() ),
			$invoice_record->postIdValue(),
			new ChainID( $invoice_record->chainIdValue() ),
			new Price(
				$invoice_record->sellingAmountHexValue(),
				$invoice_record->sellingDecimalsValue(),
				$invoice_record->sellingSymbolValue()
			),
			Address::from( $invoice_record->sellerAddressValue() ),
			Address::from( $invoice_record->paymentTokenAddressValue() ),
			$invoice_record->paymentAmountHexValue(),
			Address::from( $invoice_record->consumerAddressValue() ),
			$invoice_record->nonceValue() ? new InvoiceNonce( $invoice_record->nonceValue() ) : null
		);
	}
}
