<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository;

use Cornix\Serendipity\Core\Entity\Oracles;
use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\OracleTable;

class OracleRepository {

	public function __construct( ?OracleTable $oracle_table = null ) {
		$this->oracle_table = $oracle_table ?? new OracleTable( $GLOBALS['wpdb'] );
	}

	private OracleTable $oracle_table;

	/**
	 * データが存在するチェーン一覧(すべて)を取得します。
	 */
	public function all(): Oracles {
		$records = $this->oracle_table->all();
		return Oracles::fromTableRecords( $records );
	}
}
