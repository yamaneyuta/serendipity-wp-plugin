<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository\TableGateway;

use Cornix\Serendipity\Core\Lib\Database\MySQLiFactory;
use Cornix\Serendipity\Core\Repository\Name\TableName;
use Cornix\Serendipity\Core\ValueObject\InvoiceID;
use Cornix\Serendipity\Core\ValueObject\Price;

/**
 * 発行した請求書の情報を保存するテーブル
 */
class InvoiceTable {
	public function __construct( \wpdb $wpdb = null ) {
		$this->wpdb               = $wpdb ?? $GLOBALS['wpdb'];
		$this->invoice_table_name = ( new TableName() )->invoice();
	}

	private \wpdb $wpdb;
	private string $invoice_table_name;

	private function mysqli(): \mysqli {
		return ( new MySQLiFactory() )->create( $this->wpdb );
	}

	/**
	 * 購入用請求書テーブルを作成します。
	 */
	public function create(): void {
		$charset    = $this->wpdb->get_charset_collate();
		$index_name = "idx_{$this->invoice_table_name}_2D6F4376";

		// - 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		$sql = <<<SQL
			CREATE TABLE `{$this->invoice_table_name}` (
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

		$mysqli = $this->mysqli();
		$result = $mysqli->query( $sql );
		if ( true !== $result ) {
			throw new \RuntimeException( '[BAC2FC48] Failed to create invoice table. ' . $mysqli->error );
		}
	}

	/**
	 *
	 * @param InvoiceID $invoice_ID
	 * @return null|object{
	 *   id: string,
	 *   post_id: int,
	 *   chain_id: int,
	 *   selling_amount_hex: string,
	 *   selling_decimals: int,
	 *   selling_symbol: string,
	 *   seller_address: string,
	 *   payment_token_address: string,
	 *   payment_amount_hex: string,
	 *   consumer_address: string
	 * }
	 */
	public function select( InvoiceID $invoice_ID ) {
		$sql = <<<SQL
			SELECT *
			FROM `{$this->invoice_table_name}`
			WHERE `id` = %s
		SQL;

		$sql = $this->wpdb->prepare( $sql, $invoice_ID->ulid() );

		$result = $this->wpdb->get_row( $sql );

		if ( ! is_null( $result ) ) {
			$result->post_id          = (int) $result->post_id;
			$result->chain_id         = (int) $result->chain_id;
			$result->selling_decimals = (int) $result->selling_decimals;
		}
		return $result;
	}

	public function insert( int $post_ID, int $chain_ID, Price $selling_price, string $seller_address, string $payment_token_address, string $payment_amount_hex, string $consumer_address ) {
		$invoice_id         = InvoiceID::generate();
		$selling_amount_hex = $selling_price->amountHex();
		$selling_decimals   = $selling_price->decimals();
		$selling_symbol     = $selling_price->symbol();

		$result = $this->wpdb->insert(
			$this->invoice_table_name,
			array(
				'id'                    => $invoice_id->ulid(),
				'post_id'               => $post_ID,
				'chain_id'              => $chain_ID,
				'selling_amount_hex'    => $selling_amount_hex,
				'selling_decimals'      => $selling_decimals,
				'selling_symbol'        => $selling_symbol,
				'seller_address'        => $seller_address,
				'payment_token_address' => $payment_token_address,
				'payment_amount_hex'    => $payment_amount_hex,
				'consumer_address'      => $consumer_address,
			),
		);
		if ( false === $result || $this->wpdb->last_error ) {
			throw new \RuntimeException( '[5F99E86E] Failed to insert invoice. ' . $this->wpdb->last_error );
		}

		return $invoice_id;
	}

	/**
	 * 購入用請求書テーブルを削除します。
	 */
	public function drop(): void {
		$sql = <<<SQL
			DROP TABLE IF EXISTS `{$this->invoice_table_name}`;
		SQL;

		$result = $this->mysqli()->query( $sql );
		assert( true === $result );
	}
}
