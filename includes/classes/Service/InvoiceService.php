<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Service;

use Cornix\Serendipity\Core\Repository\TableGateway\InvoiceNonceTable;
use Cornix\Serendipity\Core\Repository\TableGateway\InvoiceTable;
use Cornix\Serendipity\Core\Entity\Invoice;
use Cornix\Serendipity\Core\ValueObject\InvoiceID;
use Cornix\Serendipity\Core\ValueObject\InvoiceNonce;
use Cornix\Serendipity\Core\ValueObject\Price;

class InvoiceService {

	public function __construct( \wpdb $wpdb = null ) {
		$this->wpdb = $wpdb ?? $GLOBALS['wpdb'];
	}

	private \wpdb $wpdb;

	private function invoiceTable(): InvoiceTable {
		return new InvoiceTable( $this->wpdb );
	}

	private function invoiceNonceTable(): InvoiceNonceTable {
		return new InvoiceNonceTable( $this->wpdb );
	}

	/**
	 * 購入用請求書を発行します。
	 *
	 * @param int    $post_ID
	 * @param int    $chain_ID
	 * @param Price  $selling_price
	 * @param string $seller_address
	 * @param string $payment_token_address
	 * @param string $payment_amount_hex
	 * @param string $consumer_address
	 *
	 * @return Invoice 発行された請求書情報
	 */
	public function issue( int $post_ID, int $chain_ID, Price $selling_price, string $seller_address, string $payment_token_address, string $payment_amount_hex, string $consumer_address ): Invoice {
		// 請求書情報を保存
		$invoice_ID = $this->invoiceTable()->insert(
			$post_ID,
			$chain_ID,
			$selling_price,
			$seller_address,
			$payment_token_address,
			$payment_amount_hex,
			$consumer_address
		);
		// 請求書に紐づくnonceを新規作成し、テーブルに保存
		$this->invoiceNonceTable()->set( $invoice_ID, new InvoiceNonce() );

		return new Invoice( $invoice_ID );
	}

	public function getData( InvoiceID $invoice_ID ): ?Invoice {
		$invoice_data = new Invoice( $invoice_ID, $this->wpdb );

		// 請求書データが存在しない場合はnullを返す
		return $invoice_data->exists() ? $invoice_data : null;
	}
}
