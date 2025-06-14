<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\TableGateway;

use Cornix\Serendipity\Core\Domain\Entity\Invoice;
use Cornix\Serendipity\Core\Repository\Name\TableName;
use Cornix\Serendipity\Core\ValueObject\InvoiceID;
use Cornix\Serendipity\Core\Infrastructure\Database\ValueObject\InvoiceTableRecord;

/**
 * 発行した請求書の情報を保存するテーブル
 */
class InvoiceTable extends TableBase {
	public function __construct( \wpdb $wpdb ) {
		parent::__construct( $wpdb, ( new TableName() )->invoice() );
	}

	/**
	 *
	 * @param InvoiceID $invoice_ID
	 * @return null|InvoiceTableRecord
	 */
	public function select( InvoiceID $invoice_ID ) {
		$sql = <<<SQL
			SELECT
				`id`,
				`post_id`,
				`chain_id`,
				`selling_amount_hex`,
				`selling_decimals`,
				`selling_symbol`,
				`seller_address`,
				`payment_token_address`,
				`payment_amount_hex`,
				`consumer_address`,
				`nonce`
			FROM `{$this->tableName()}`
			WHERE `id` = %s
		SQL;

		$sql = $this->wpdb()->prepare( $sql, $invoice_ID->ulid() );

		$record = $this->wpdb()->get_row( $sql );
		if ( null !== $record ) {
			$record->post_id          = (int) $record->post_id;
			$record->chain_id         = (int) $record->chain_id;
			$record->selling_decimals = (int) $record->selling_decimals;
		}

		return is_null( $record ) ? null : new InvoiceTableRecord( $record );
	}

	public function insert( Invoice $invoice ): void {
		$result = $this->wpdb()->insert(
			$this->tableName(),
			array(
				'id'                    => $invoice->id()->ulid(),
				'post_id'               => $invoice->postID(),
				'chain_id'              => $invoice->chainID()->value(),
				'selling_amount_hex'    => $invoice->sellingPrice()->amountHex(),
				'selling_decimals'      => $invoice->sellingPrice()->decimals(),
				'selling_symbol'        => $invoice->sellingPrice()->symbol(),
				'seller_address'        => $invoice->sellerAddress()->value(),
				'payment_token_address' => $invoice->paymentTokenAddress()->value(),
				'payment_amount_hex'    => $invoice->paymentAmountHex(),
				'consumer_address'      => $invoice->consumerAddress()->value(),
				'nonce'                 => $invoice->nonce() ? $invoice->nonce()->value() : null,
			),
		);
		if ( false === $result || $this->wpdb()->last_error ) {
			throw new \RuntimeException( '[5F99E86E] Failed to insert invoice. ' . $this->wpdb()->last_error );
		}
	}
}
