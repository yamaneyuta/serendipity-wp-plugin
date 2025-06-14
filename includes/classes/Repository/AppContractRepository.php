<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository;

use Cornix\Serendipity\Core\Domain\Entity\AppContract;
use Cornix\Serendipity\Core\Infrastructure\Database\Entity\AppContractImpl;
use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\AppContractTable;
use Cornix\Serendipity\Core\ValueObject\ChainID;

class AppContractRepository {
	public function __construct( ?AppContractTable $app_contract_table = null, ?ChainRepository $chain_repository = null ) {
		$this->app_contract_table = $app_contract_table ?? new AppContractTable( $GLOBALS['wpdb'] );
		$this->chain_repository   = $chain_repository ?? new ChainRepository();
	}
	private AppContractTable $app_contract_table;
	private ChainRepository $chain_repository;

	public function get( ChainID $chain_id ): ?AppContract {
		$records = $this->app_contract_table->all();
		$records = array_filter(
			$records,
			fn( $record ) => $record->chainID() === $chain_id->value()
		);
		assert( count( $records ) <= 1, '[68E05B97] should return at most one record.' );

		return empty( $records ) ? null : AppContractImpl::fromTableRecord(
			$this->chain_repository->getChain( $chain_id ),
			array_values( $records )[0]
		);
	}
}
