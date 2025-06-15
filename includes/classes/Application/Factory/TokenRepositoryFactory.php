<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Factory;

use Cornix\Serendipity\Core\Infrastructure\Database\Repository\TokenRepository;
use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\TokenTable;

class TokenRepositoryFactory {
	public function __construct( \wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	private \wpdb $wpdb;

	public function create(): TokenRepository {
		$table      = new TokenTable( $this->wpdb );
		$repository = new TokenRepository( $table );
		return $repository;
	}
}
