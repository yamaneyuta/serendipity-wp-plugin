<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Name\TableName;
use Cornix\Serendipity\Core\Types\Price;
use yamaneyuta\Ulid;

class Invoice {
	public function __construct( \wpdb $wpdb ) {
		$this->wpdb       = $wpdb;
		$this->table_name = ( new TableName() )->invoice();
	}

	private \wpdb $wpdb;
	private string $table_name;

	public function issue( Price $selling_price ): string {
		$invoice_id         = ( new Ulid() )->toUuid();
		$selling_amount_hex = $selling_price->amountHex();
		$selling_decimals   = $selling_price->decimals();
		$selling_symbol     = $selling_price->symbol();

		$sql = <<<SQL
			INSERT INTO `{$this->table_name}`
			(`id`, `selling_amount_hex`, `selling_decimals`, `selling_symbol`)
			VALUES (%s, %s, %d, %s)
		SQL;

		$sql = $this->wpdb->prepare( $sql, $invoice_id, $selling_amount_hex, $selling_decimals, $selling_symbol );

		$result = $this->wpdb->query( $sql );
		assert( false !== $result );

		return $invoice_id;
	}
}
