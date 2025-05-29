<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Entity;

use Cornix\Serendipity\Core\Repository\TableGateway\InvoiceNonceTable;
use Cornix\Serendipity\Core\Repository\TableGateway\InvoiceTable;
use Cornix\Serendipity\Core\ValueObject\InvoiceID;
use Cornix\Serendipity\Core\ValueObject\InvoiceNonce;

class Invoice {
	public function __construct( InvoiceID $invoice_ID, \wpdb $wpdb = null ) {
		$this->invoice_ID = $invoice_ID;
		$this->wpdb       = $wpdb;
	}

	private InvoiceID $invoice_ID;

	/** @var null|\wpdb */
	private $wpdb;

	private function record() {
		return ( new InvoiceTable( $this->wpdb ) )->select( $this->invoice_ID );
	}

	/**
	 * コンストラクタで指定されたIDの請求書情報が存在するかどうかを返します。
	 */
	public function exists(): bool {
		return ! is_null( $this->record() );
	}

	public function id(): InvoiceID {
		return $this->invoice_ID;
	}

	public function nonce(): ?InvoiceNonce {
		return ( new InvoiceNonceTable( $this->wpdb ) )->getNonce( $this->invoice_ID );
	}

	/**
	 * 請求書が発行された投稿IDを取得します。
	 */
	public function postID(): int {
		$record = $this->record();
		assert( property_exists( $record, 'post_id' ), '[1196F8E0] requires the record to have a post_id property.' );
		assert( is_int( $record->post_id ), '[3CCDB8FC] post_id must be an integer.' );

		return $record->post_id;
	}

	public function chainID(): int {
		$record = $this->record();
		assert( property_exists( $record, 'chain_id' ), '[53F6B40C] requires the record to have a chain_id property.' );
		assert( is_int( $record->chain_id ), '[165D1295] chain_id must be an integer.' );

		return $record->chain_id;
	}

	public function consumerAddress(): string {
		$record = $this->record();
		assert( property_exists( $record, 'consumer_address' ), '[8BA56ACE] requires the record to have a consumer_address property.' );
		assert( is_string( $record->consumer_address ), '[A30BDDD7] consumer_address must be a string.' );

		return $record->consumer_address;
	}
}
