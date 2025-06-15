<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Factory;

use Cornix\Serendipity\Core\Infrastructure\Database\Repository\PostRepository;
use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\PaidContentTable;

class PostRepositoryFactory {
	public function __construct( \wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	private \wpdb $wpdb;

	public function create(): PostRepository {
		$table      = new PaidContentTable( $this->wpdb );
		$repository = new PostRepository( $table );
		return $repository;
	}
}
