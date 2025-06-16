<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\Repository;

use Cornix\Serendipity\Core\Domain\Entity\Chain;
use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Infrastructure\Database\Entity\ChainImpl;
use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\ChainTable;
use Cornix\Serendipity\Core\Domain\Specification\ChainsFilter;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;

class ChainRepositoryImpl implements ChainRepository {

	public function __construct( ChainTable $chain_table ) {
		$this->chain_table = $chain_table;
	}

	private ChainTable $chain_table;

	/** @inheritdoc */
	public function get( ChainID $chain_id ): ?Chain {
		$chains          = $this->all();
		$chains_filter   = ( new ChainsFilter() )->byChainID( $chain_id );
		$filtered_chains = $chains_filter->apply( $chains );
		assert( count( $filtered_chains ) <= 1, '[BB8A90CF] should return at most one record.' );
		return empty( $filtered_chains ) ? null : array_values( $filtered_chains )[0];
	}

	/** @inheritdoc */
	public function all(): array {
		$records = $this->chain_table->all();

		return array_values(
			array_map(
				fn( $record ) => ChainImpl::fromTableRecord( $record ),
				$records
			)
		);
	}

	/** @inheritdoc */
	public function save( Chain $chain ): void {
		$this->chain_table->save( $chain );
	}
}
