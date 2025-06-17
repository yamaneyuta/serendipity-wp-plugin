<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Factory;

use Cornix\Serendipity\Core\Domain\Repository\TokenRepository;
use Cornix\Serendipity\Core\Infrastructure\Database\Repository\TokenRepositoryImpl;
use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\TokenTable;

class TokenRepositoryFactory {
	public function __construct( \wpdb $wpdb = null ) {
		$this->wpdb = $wpdb ?? $GLOBALS['wpdb'];
	}

	private \wpdb $wpdb;

	public function create(): TokenRepository {
		$table      = new TokenTable( $this->wpdb );
		$repository = new TokenRepositoryImpl( $table );
		return $repository;
	}
}
