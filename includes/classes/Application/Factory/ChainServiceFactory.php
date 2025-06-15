<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Factory;

use Cornix\Serendipity\Core\Application\Service\ChainService;

class ChainServiceFactory {
	public function __construct( \wpdb $wpdb = null ) {
		$this->wpdb = $wpdb ?? $GLOBALS['wpdb'];
	}

	private \wpdb $wpdb;

	public function create(): ChainService {
		return new ChainService( ( new ChainRepositoryFactory( $this->wpdb ) )->create() );
	}
}
