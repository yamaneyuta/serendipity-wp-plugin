<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Factory;

use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\ServerSignerTable;
use Cornix\Serendipity\Core\Repository\ServerSignerPrivateKeyRepository;
use Cornix\Serendipity\Core\Application\Service\ServerSignerService;

class ServerSignerServiceFactory {
	public function create( \wpdb $wpdb ): ServerSignerService {
		$table      = new ServerSignerTable( $wpdb );
		$repository = new ServerSignerPrivateKeyRepository( $table );
		return new ServerSignerService( $repository );
	}
}
