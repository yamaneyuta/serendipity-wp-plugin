<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Database\TableName;
use yamaneyuta\Ulid;

class PurchaseTicket {
	public function __construct( \wpdb $wpdb ) {
		$this->wpdb       = $wpdb;
		$this->table_name = ( new TableName() )->purchaseTicket();
	}

	private \wpdb $wpdb;
	private string $table_name;

	public function issue( string $selling_amount_hex, int $selling_decimals, string $selling_symbol ): string {
		$ticket_id = ( new Ulid() )->toUuid();

		$sql = <<<SQL
			INSERT INTO `{$this->table_name}`
			(`ticket_id`, `selling_amount_hex`, `selling_decimals`, `selling_symbol`)
			VALUES (%s, %s, %d, %s)
		SQL;

		$sql = $this->wpdb->prepare( $sql, $ticket_id, $selling_amount_hex, $selling_decimals, $selling_symbol );

		$result = $this->wpdb->query( $sql );
		assert( false !== $result );

		return $ticket_id;
	}
}
