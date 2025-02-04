<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Name\TableName;
use Cornix\Serendipity\Core\Types\InvoiceIdType;
use Cornix\Serendipity\Core\Types\Price;

class Invoice {
	public function __construct( \wpdb $wpdb ) {
		$this->wpdb       = $wpdb;
		$this->table_name = ( new TableName() )->invoice();
	}

	private \wpdb $wpdb;
	private string $table_name;

	public function issue( int $post_ID, int $chain_ID, Price $selling_price, string $seller_address, string $consumer_address ): InvoiceIdType {
		$invoice_id         = InvoiceIdType::generate();
		$selling_amount_hex = $selling_price->amountHex();
		$selling_decimals   = $selling_price->decimals();
		$selling_symbol     = $selling_price->symbol();

		$sql = <<<SQL
			INSERT INTO `{$this->table_name}`
			(`id`, `post_id`, `chain_id`, `selling_amount_hex`, `selling_decimals`, `selling_symbol`, `seller_address`, `consumer_address`)
			VALUES (%s, %d, %d, %s, %d, %s, %s, %s)
		SQL;

		$sql = $this->wpdb->prepare( $sql, $invoice_id->ulid(), $post_ID, $chain_ID, $selling_amount_hex, $selling_decimals, $selling_symbol, $seller_address, $consumer_address );

		$result = $this->wpdb->query( $sql );
		assert( 1 === $result );

		return $invoice_id;
	}

	public function get( InvoiceIdType $invoice_ID ): ?InvoiceData {
		$sql = <<<SQL
			SELECT *
			FROM `{$this->table_name}`
			WHERE `id` = %s
		SQL;

		$sql = $this->wpdb->prepare( $sql, $invoice_ID->ulid() );

		$result = $this->wpdb->get_row( $sql, ARRAY_A );

		return is_array( $result ) ? new InvoiceData( $result ) : null;
	}
}


/** @internal */
class InvoiceData {
	public function __construct( array $data ) {
		$this->data = $data;
	}

	private array $data;

	public function postID(): int {
		return (int) $this->data['post_id'];
	}

	public function chainID(): int {
		return (int) $this->data['chain_id'];
	}

	public function consumerAddress(): string {
		return $this->data['consumer_address'];
	}
}
