<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\TableGateway;

use Cornix\Serendipity\Core\Repository\Name\TableName;
use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\Infrastructure\Database\ValueObject\OracleTableRecord;

/**
 * Oracleの情報を記録するテーブル
 */
class OracleTable extends TableBase {

	public function __construct( \wpdb $wpdb ) {
		parent::__construct( $wpdb, ( new TableName() )->oracle() );
	}

	/**
	 * Oracleデータ一覧を取得します。
	 *
	 * @return OracleTableRecord[]
	 */
	public function all(): array {
		// Oracleのデータ量は少ないので絞り込みは上位で行う
		$sql = <<<SQL
			SELECT `chain_id`, `address`, `base_symbol`, `quote_symbol`
			FROM `{$this->tableName()}`
		SQL;

		$result = $this->wpdb()->get_results( $sql );
		if ( false === $result ) {
			throw new \Exception( '[AE20156F] Failed to get oracle data.' );
		}

		$records = array();
		foreach ( $result as $row ) {
			$row->chain_id     = (int) $row->chain_id;
			$row->address      = (string) $row->address;
			$row->base_symbol  = (string) $row->base_symbol;
			$row->quote_symbol = (string) $row->quote_symbol;

			$records[] = new OracleTableRecord( $row );
		}

		return $records;
	}

	/**
	 * テーブルにOracleを追加します。
	 */
	public function insert( int $chain_ID, string $address, string $base_symbol, string $quote_symbol ): void {
		Validate::checkChainID( $chain_ID );
		Validate::checkSymbol( $base_symbol );
		Validate::checkSymbol( $quote_symbol );

		$sql = <<<SQL
			INSERT INTO `{$this->tableName()}`
			(`chain_id`, `address`, `base_symbol`, `quote_symbol`)
			VALUES (%d, %s, %s, %s)
		SQL;

		$sql = $this->wpdb()->prepare( $sql, $chain_ID, $address, $base_symbol, $quote_symbol );

		$result = $this->wpdb()->query( $sql );
		if ( false === $result ) {
			throw new \Exception( '[91E6A6C0] Failed to add oracle data.' );
		}
	}
}
