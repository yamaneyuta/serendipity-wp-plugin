<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\UseCase;

use Cornix\Serendipity\Core\Application\Factory\InvoiceRepositoryFactory;
use Cornix\Serendipity\Core\Domain\Entity\Invoice;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceID;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceNonce;
use Cornix\Serendipity\Core\Domain\ValueObject\Price;
use wpdb;

class IssueInvoice {
	public function __construct( wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}
	private wpdb $wpdb;

	public function handle( int $post_ID, ChainID $chain_ID, Price $selling_price, Address $seller_address, Address $payment_token_address, string $payment_amount_hex, Address $consumer_address ): Invoice {
		$invoice_repository = ( new InvoiceRepositoryFactory( $this->wpdb ) )->create();

		$invoice = new Invoice(
			InvoiceID::generate(), // 新規請求書ID
			$post_ID,
			$chain_ID,
			$selling_price,
			$seller_address,
			$payment_token_address,
			$payment_amount_hex,
			$consumer_address,
			InvoiceNonce::generate() // 新規nonce
		);
		assert( $invoice_repository->exists( $invoice->id() ) === false, '[A9E90E49] Duplicate invoice ID detected.' );

		// 請求書情報を保存
		$invoice_repository->add( $invoice );

		return $invoice;
	}
}
