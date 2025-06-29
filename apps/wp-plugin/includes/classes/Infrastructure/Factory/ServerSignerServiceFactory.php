<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Factory;

use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\ServerSignerTable;
use Cornix\Serendipity\Core\Infrastructure\Database\Repository\ServerSignerPrivateKeyRepository;
use Cornix\Serendipity\Core\Application\Service\ServerSignerService;

class ServerSignerServiceFactory {
	public function __construct( \wpdb $wpdb = null ) {
		$this->wpdb = $wpdb ?? $GLOBALS['wpdb'];
	}

	private \wpdb $wpdb;

	public function create(): ServerSignerService {
		$table      = new ServerSignerTable( $this->wpdb );
		$repository = new ServerSignerPrivateKeyRepository( $table );
		return new ServerSignerService( $repository );
	}
}
