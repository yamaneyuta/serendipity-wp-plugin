<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository;

use Cornix\Serendipity\Core\Domain\Entity\Oracle;
use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\ChainTable;
use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\OracleTable;

class OracleRepository {

	public function __construct( ?OracleTable $oracle_table = null ) {
		$this->oracle_table = $oracle_table ?? new OracleTable( $GLOBALS['wpdb'] );
		$this->chain_table  = new ChainTable( $GLOBALS['wpdb'] );
	}

	private OracleTable $oracle_table;
	private ChainTable $chain_table;

	/**
	 * Repositoryに存在するOracle一覧を取得します。
	 *
	 * @return Oracle[]
	 */
	public function all(): array {
		$oracle_records = $this->oracle_table->all();

		/** @var Oracle[] */
		$results = array();
		foreach ( $oracle_records as $record ) {
			$chain_record = $this->chain_table->select( $record->chainID() );
			$results[]    = Oracle::fromTableRecord( $record, array_values( $chain_record )[0] );
		}

		return $results;
	}
}
