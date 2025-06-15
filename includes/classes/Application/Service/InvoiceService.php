<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

use Cornix\Serendipity\Core\Domain\Entity\Invoice;
use Cornix\Serendipity\Core\Infrastructure\Database\Repository\InvoiceRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceID;
use Cornix\Serendipity\Core\Domain\ValueObject\InvoiceNonce;
use Cornix\Serendipity\Core\Domain\ValueObject\Price;

class InvoiceService {

	public function __construct( ?InvoiceRepository $invoice_repository = null ) {
		$this->invoice_repository = $invoice_repository ?? new InvoiceRepository();
	}
	private InvoiceRepository $invoice_repository;

	/**
	 * 購入用請求書を発行します。
	 *
	 * @param int     $post_ID
	 * @param ChainID $chain_ID
	 * @param Price   $selling_price
	 * @param Address $seller_address
	 * @param Address $payment_token_address
	 * @param string  $payment_amount_hex
	 * @param Address $consumer_address
	 *
	 * @return Invoice 発行された請求書情報
	 */
	public function issue( int $post_ID, ChainID $chain_ID, Price $selling_price, Address $seller_address, Address $payment_token_address, string $payment_amount_hex, Address $consumer_address ): Invoice {

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
		assert( $this->invoice_repository->exists( $invoice->id() ) === false, '[A9E90E49] Duplicate invoice ID detected.' );

		// 請求書情報を保存
		$this->invoice_repository->add( $invoice );

		return $invoice;
	}
}
