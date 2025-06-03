<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository\TableGateway;

use Cornix\Serendipity\Core\Lib\Database\TableBase;
use Cornix\Serendipity\Core\Repository\Name\TableName;
use Cornix\Serendipity\Core\ValueObject\Address;
use Cornix\Serendipity\Core\ValueObject\InvoiceID;
use Cornix\Serendipity\Core\ValueObject\Price;
use Cornix\Serendipity\Core\ValueObject\TableRecord\InvoiceTableRecord;

/**
 * 発行した請求書の情報を保存するテーブル
 */
class InvoiceTable extends TableBase {
	public function __construct( \wpdb $wpdb ) {
		parent::__construct( $wpdb, ( new TableName() )->invoice() );
	}

	/**
	 * @inheritdoc
	 * 購入用請求書テーブルを作成します。
	 */
	public function create(): void {
		$charset    = $this->wpdb()->get_charset_collate();
		$index_name = "idx_{$this->tableName()}_2D6F4376";

		// - 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		$sql = <<<SQL
			CREATE TABLE `{$this->tableName()}` (
				`created_at`             timestamp               NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`id`                     varchar(191)            NOT NULL,
				`post_id`			     bigint        unsigned  NOT NULL,
				`chain_id`               bigint        unsigned  NOT NULL,
				`selling_amount_hex`     varchar(191)            NOT NULL,
				`selling_decimals`       int                     NOT NULL,
				`selling_symbol`         varchar(191)            NOT NULL,
				`seller_address`         varchar(191)            NOT NULL,
				`payment_token_address`  varchar(191)            NOT NULL,
				`payment_amount_hex`     varchar(191)            NOT NULL,
				`consumer_address`       varchar(191)            NOT NULL,
				PRIMARY KEY (`id`),
				KEY `{$index_name}` (`created_at`)
			) {$charset};
		SQL;

		$result = $this->mysqli()->query( $sql );
		if ( true !== $result ) {
			throw new \RuntimeException( '[BAC2FC48] Failed to create invoice table. ' . $this->mysqli()->error );
		}
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
				`consumer_address`
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

	public function insert( InvoiceID $invoice_id, int $post_ID, int $chain_ID, Price $selling_price, Address $seller_address, Address $payment_token_address, string $payment_amount_hex, Address $consumer_address ) {
		$selling_amount_hex = $selling_price->amountHex();
		$selling_decimals   = $selling_price->decimals();
		$selling_symbol     = $selling_price->symbol();

		$result = $this->wpdb()->insert(
			$this->tableName(),
			array(
				'id'                    => $invoice_id->ulid(),
				'post_id'               => $post_ID,
				'chain_id'              => $chain_ID,
				'selling_amount_hex'    => $selling_amount_hex,
				'selling_decimals'      => $selling_decimals,
				'selling_symbol'        => $selling_symbol,
				'seller_address'        => $seller_address->value(),
				'payment_token_address' => $payment_token_address->value(),
				'payment_amount_hex'    => $payment_amount_hex,
				'consumer_address'      => $consumer_address->value(),
			),
		);
		if ( false === $result || $this->wpdb()->last_error ) {
			throw new \RuntimeException( '[5F99E86E] Failed to insert invoice. ' . $this->wpdb()->last_error );
		}

		return $invoice_id;
	}
}
