<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Name\TableName;
use Cornix\Serendipity\Core\Types\InvoiceID;
use Cornix\Serendipity\Core\Types\Price;

class Invoice {
	public function __construct( \wpdb $wpdb ) {
		$this->wpdb       = $wpdb;
		$this->table_name = ( new TableName() )->invoice();
	}

	private \wpdb $wpdb;
	private string $table_name;

	public function issue( int $post_ID, int $chain_ID, Price $selling_price, string $consumer_address ): InvoiceID {
		$invoice_id         = InvoiceID::generate();
		$selling_amount_hex = $selling_price->amountHex();
		$selling_decimals   = $selling_price->decimals();
		$selling_symbol     = $selling_price->symbol();

		$sql = <<<SQL
			INSERT INTO `{$this->table_name}`
			(`id`, `post_id`, `chain_id`, `selling_amount_hex`, `selling_decimals`, `selling_symbol`, `consumer_address`)
			VALUES (%s, %d, %d, %s, %d, %s, %s)
		SQL;

		$sql = $this->wpdb->prepare( $sql, $invoice_id->ulid(), $post_ID, $chain_ID, $selling_amount_hex, $selling_decimals, $selling_symbol, $consumer_address );

		$result = $this->wpdb->query( $sql );
		assert( false !== $result );

		return $invoice_id;
	}
}
