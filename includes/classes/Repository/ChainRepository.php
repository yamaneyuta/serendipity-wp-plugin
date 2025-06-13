<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository;

use Cornix\Serendipity\Core\Domain\Entity\Chain;
use Cornix\Serendipity\Core\Infrastructure\Database\Entity\ChainImpl;
use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\ChainTable;
use Cornix\Serendipity\Core\Domain\Specification\ChainsFilter;

class ChainRepository {

	public function __construct( ?ChainTable $chain_table = null ) {
		$this->chain_table = $chain_table ?? new ChainTable( $GLOBALS['wpdb'] );
	}

	private ChainTable $chain_table;

	/**
	 * 指定したチェーンIDに合致するチェーンを取得します。
	 */
	public function getChain( int $chain_id ): ?Chain {

		$chains          = $this->getAllChains();
		$chains_filter   = ( new ChainsFilter() )->byChainID( $chain_id );
		$filtered_chains = $chains_filter->apply( $chains );
		assert( count( $filtered_chains ) <= 1, '[BB8A90CF] should return at most one record.' );
		return empty( $filtered_chains ) ? null : array_values( $filtered_chains )[0];
	}

	/**
	 * データが存在するチェーン一覧(すべて)を取得します。
	 *
	 * @return Chain[]
	 */
	public function getAllChains() {
		$records = $this->chain_table->select();

		return array_values(
			array_map(
				fn( $record ) => ChainImpl::fromTableRecord( $record ),
				$records
			)
		);
	}

	public function save( Chain $chain ): void {
		$this->chain_table->save( $chain );
	}
}
