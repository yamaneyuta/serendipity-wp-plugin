<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository\TableGateway;

use Cornix\Serendipity\Core\Lib\Database\MySQLiFactory;
use Cornix\Serendipity\Core\Repository\Name\TableName;
use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\Entity\Oracle;
use Cornix\Serendipity\Core\ValueObject\Address;

/**
 * Oracleの情報を記録するテーブル
 */
class OracleTable {

	public function __construct( \wpdb $wpdb = null ) {
		$this->wpdb       = $wpdb ?? $GLOBALS['wpdb'];
		$this->mysqli     = ( new MySQLiFactory() )->create( $this->wpdb );
		$this->table_name = ( new TableName() )->oracle();
	}

	private \wpdb $wpdb;
	private \mysqli $mysqli;
	private string $table_name;

	/**
	 * テーブルを作成します。
	 */
	public function create(): void {
		$charset         = $this->wpdb->get_charset_collate();
		$unique_key_name = "uq_{$this->table_name}_C269159C";

		$sql = <<<SQL
			CREATE TABLE `{$this->table_name}` (
				`created_at`     timestamp               NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`updated_at`     timestamp               NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`chain_id`       bigint        unsigned  NOT NULL,
				`address`        varchar(191)            NOT NULL,
				`base_symbol`    varchar(191)            NOT NULL,
				`quote_symbol`   varchar(191)            NOT NULL,
				PRIMARY KEY (`chain_id`, `address`),
				UNIQUE KEY `{$unique_key_name}` (`chain_id`, `base_symbol`, `quote_symbol`)
			) {$charset};
		SQL;

		$result = $this->mysqli->query( $sql );
		assert( true === $result );
	}

	/**
	 * Oracleデータ一覧を取得します。
	 * パラメータでチェーンID、アドレス、シンボルを指定することで絞り込みができます。
	 *
	 * @param int|null    $chain_ID チェーンID
	 * @param string|null $address Oracleアドレス
	 * @param string|null $base_symbol ベースシンボル
	 * @param string|null $quote_symbol クォートシンボル
	 * @return Oracle[]
	 */
	public function select( ?int $chain_ID = null, ?string $address = null, ?string $base_symbol = null, ?string $quote_symbol = null ): array {
		$sql = <<<SQL
			SELECT `chain_id`, `address`, `base_symbol`, `quote_symbol`
			FROM `{$this->table_name}`
		SQL;

		// 条件がある場合はWHERE句を追加
		$wheres = array();
		if ( ! is_null( $chain_ID ) ) {
			Validate::checkChainID( $chain_ID );
			$wheres[] = $this->wpdb->prepare( '`chain_id` = %d', $chain_ID );
		}
		if ( ! is_null( $address ) ) {
			$wheres[] = $this->wpdb->prepare( '`address` = %s', $address );
		}
		if ( ! is_null( $base_symbol ) ) {
			Validate::checkSymbol( $base_symbol );
			$wheres[] = $this->wpdb->prepare( '`base_symbol` = %s', $base_symbol );
		}
		if ( ! is_null( $quote_symbol ) ) {
			Validate::checkSymbol( $quote_symbol );
			$wheres[] = $this->wpdb->prepare( '`quote_symbol` = %s', $quote_symbol );
		}

		if ( ! empty( $wheres ) ) {
			$sql .= ' WHERE ' . implode( ' AND ', $wheres );
		}

		$result = $this->wpdb->get_results( $sql );
		if ( false === $result ) {
			throw new \Exception( '[AE20156F] Failed to get oracle data.' );
		}

		$records = array();
		foreach ( $result as $row ) {
			$chain_ID     = (int) $row->chain_id;
			$address      = (string) $row->address;
			$base_symbol  = (string) $row->base_symbol;
			$quote_symbol = (string) $row->quote_symbol;

			assert( Validate::isChainID( $chain_ID ), '[75C4111A] Invalid chain ID. ' . $chain_ID );
			assert( Validate::isSymbol( $base_symbol ), '[7F884B25] Invalid base symbol. ' . $base_symbol );
			assert( Validate::isSymbol( $quote_symbol ), '[9A0090FB] Invalid quote symbol. ' . $quote_symbol );

			$records[] = Oracle::from( $chain_ID, Address::from( $address ), $base_symbol, $quote_symbol );
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
			INSERT INTO `{$this->table_name}`
			(`chain_id`, `address`, `base_symbol`, `quote_symbol`)
			VALUES (%d, %s, %s, %s)
		SQL;

		$sql = $this->wpdb->prepare( $sql, $chain_ID, $address, $base_symbol, $quote_symbol );

		$result = $this->wpdb->query( $sql );
		if ( false === $result ) {
			throw new \Exception( '[91E6A6C0] Failed to add oracle data.' );
		}
	}

	/**
	 * テーブルを削除します。
	 */
	public function drop(): void {
		$sql = <<<SQL
			DROP TABLE IF EXISTS `{$this->table_name}`;
		SQL;

		$result = $this->mysqli->query( $sql );
		assert( true === $result );
	}
}
