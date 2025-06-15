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

	public function __construct( InvoiceTableRecord $invoice_record ) {
		parent::__construct(
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

	public static function fromTableRecord( InvoiceTableRecord $invoice_record ): self {
		return new self( $invoice_record );
	}
}
