<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository;

use Cornix\Serendipity\Core\Entity\Chain;
use Cornix\Serendipity\Core\Repository\TableGateway\ChainTable;

class ChainRepository {

	public function __construct( ?ChainTable $chain_table = null ) {
		$this->chain_table = $chain_table ?? new ChainTable( $GLOBALS['wpdb'] );
	}

	private ChainTable $chain_table;

	/**
	 * 指定したチェーンIDに合致するチェーンを取得します。
	 */
	public function getChain( int $chain_id ): ?Chain {
		$records = $this->chain_table->select( $chain_id );
		assert( count( $records ) <= 1, '[1605F71E] should return at most one record.' );

		return empty( $records ) ? null : Chain::fromTableRecord( $records[0] );
	}

	/**
	 * データが存在するチェーン一覧(すべて)を取得します。
	 */
	public function getAllChains() {
		$records = $this->chain_table->select();

		return array_values(
			array_map(
				fn( $record ) => Chain::fromTableRecord( $record ),
				$records
			)
		);
	}
}
