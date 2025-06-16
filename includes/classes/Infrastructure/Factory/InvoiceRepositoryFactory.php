<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Factory;

use Cornix\Serendipity\Core\Infrastructure\Database\Repository\InvoiceRepository;
use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\InvoiceTable;

class InvoiceRepositoryFactory {
	public function __construct( \wpdb $wpdb = null ) {
		$this->wpdb = $wpdb ?? $GLOBALS['wpdb'];
	}

	private \wpdb $wpdb;

	public function create(): InvoiceRepository {
		$table = new InvoiceTable( $this->wpdb );
		return new InvoiceRepository( $table );
	}
}
