<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Factory;

use Cornix\Serendipity\Core\Domain\Repository\OracleRepository;
use Cornix\Serendipity\Core\Infrastructure\Database\Repository\OracleRepositoryImpl;
use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\OracleTable;

class OracleRepositoryFactory {
	public function __construct( \wpdb $wpdb = null ) {
		$this->wpdb = $wpdb ?? $GLOBALS['wpdb'];
	}

	private \wpdb $wpdb;

	public function create(): OracleRepository {
		$table = new OracleTable( $this->wpdb );
		return new OracleRepositoryImpl( $table );
	}
}
