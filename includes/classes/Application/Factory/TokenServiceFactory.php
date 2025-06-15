<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Factory;

use Cornix\Serendipity\Core\Application\Service\TokenService;
use Cornix\Serendipity\Core\Infrastructure\Database\Repository\ChainRepository;
use Cornix\Serendipity\Core\Infrastructure\Database\Repository\TokenRepository;
use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\ChainTable;
use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\TokenTable;

class TokenServiceFactory {
	public function __construct( \wpdb $wpdb = null ) {
		$this->wpdb = $wpdb ?? $GLOBALS['wpdb'];
	}

	private \wpdb $wpdb;

	public function create(): TokenService {
		$table            = new TokenTable( $this->wpdb );
		$repository       = new TokenRepository( $table );
		$chain_table      = new ChainTable( $this->wpdb );
		$chain_repository = new ChainRepository( $chain_table );
		return new TokenService( $repository, $chain_repository );
	}
}
