<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Factory;

use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\ChainTable;
use Cornix\Serendipity\Core\Infrastructure\Database\Repository\ChainRepository;
use Cornix\Serendipity\Core\Application\Service\ChainService;

class ChainServiceFactory {
	public function __construct( \wpdb $wpdb = null ) {
		$this->wpdb = $wpdb ?? $GLOBALS['wpdb'];
	}

	private \wpdb $wpdb;

	public function create(): ChainService {
		$table      = new ChainTable( $this->wpdb );
		$repository = new ChainRepository( $table );
		return new ChainService( $repository );
	}
}
