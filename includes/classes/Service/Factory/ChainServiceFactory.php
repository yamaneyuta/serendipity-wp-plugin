<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Service\Factory;

use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\ChainTable;
use Cornix\Serendipity\Core\Repository\ChainRepository;
use Cornix\Serendipity\Core\Service\ChainService;

class ChainServiceFactory {
	public function create( \wpdb $wpdb ): ChainService {
		$table      = new ChainTable( $wpdb );
		$repository = new ChainRepository( $table );
		return new ChainService( $repository );
	}
}
