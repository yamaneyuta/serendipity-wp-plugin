<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\Repository;

use Cornix\Serendipity\Core\Domain\Entity\AppContract;
use Cornix\Serendipity\Core\Domain\Repository\AppContractRepository;
use Cornix\Serendipity\Core\Infrastructure\Database\Entity\AppContractImpl;
use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\AppContractTable;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;

class AppContractRepositoryImpl implements AppContractRepository {
	public function __construct( AppContractTable $app_contract_table, ChainRepository $chain_repository ) {
		$this->app_contract_table = $app_contract_table;
		$this->chain_repository   = $chain_repository;
	}
	private AppContractTable $app_contract_table;
	private ChainRepository $chain_repository;

	/** @inheritdoc */
	public function get( ChainID $chain_id ): ?AppContract {
		$records = $this->app_contract_table->all();
		$records = array_filter(
			$records,
			fn( $record ) => $record->chainIdValue() === $chain_id->value()
		);
		assert( count( $records ) <= 1, '[68E05B97] should return at most one record.' );

		return empty( $records ) ? null : AppContractImpl::fromTableRecord(
			$this->chain_repository->get( $chain_id ),
			array_values( $records )[0]
		);
	}

	/** @inheritdoc */
	public function save( AppContract $app_contract ): void {
		$this->app_contract_table->save( $app_contract );
	}
}
