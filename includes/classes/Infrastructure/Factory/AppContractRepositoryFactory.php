<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Factory;

use Cornix\Serendipity\Core\Infrastructure\Database\Repository\AppContractRepository;
use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\AppContractTable;

class AppContractRepositoryFactory {
	public function __construct( \wpdb $wpdb = null ) {
		$this->wpdb = $wpdb ?? $GLOBALS['wpdb'];
	}

	private \wpdb $wpdb;

	public function create(): AppContractRepository {
		$chain_repository   = ( new ChainRepositoryFactory( $this->wpdb ) )->create();
		$app_contract_table = new AppContractTable( $this->wpdb );
		return new AppContractRepository( $app_contract_table, $chain_repository );
	}
}
