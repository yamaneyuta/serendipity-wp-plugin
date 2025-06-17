<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Factory;

use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\ChainTable;
use Cornix\Serendipity\Core\Infrastructure\Database\Repository\ChainRepositoryImpl;

class ChainRepositoryFactory {
	public function __construct( \wpdb $wpdb = null ) {
		$this->wpdb = $wpdb ?? $GLOBALS['wpdb'];
	}

	private \wpdb $wpdb;

	public function create(): ChainRepository {
		$table = new ChainTable( $this->wpdb );
		return new ChainRepositoryImpl( $table );
	}
}
