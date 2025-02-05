<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Database\Schema;

use Cornix\Serendipity\Core\Lib\Database\MySQLiFactory;
use Cornix\Serendipity\Core\Lib\Repository\Name\TableName;
use Cornix\Serendipity\Core\Types\AppContractType;

/**
 * アプリケーションコントラクトの情報を保存するテーブル
 */
class AppContractTable {
	public function __construct( \wpdb $wpdb = null ) {
		$this->wpdb       = $wpdb ?? $GLOBALS['wpdb'];
		$this->mysqli     = ( new MySQLiFactory() )->create( $wpdb );
		$this->table_name = ( new TableName() )->appContract();
	}

	private \wpdb $wpdb;
	private \mysqli $mysqli;
	private string $table_name;

	/**
	 * アプリケーションコントラクトテーブルを作成します。
	 */
	public function create(): void {
		$charset = $this->wpdb->get_charset_collate();

		$sql = <<<SQL
			CREATE TABLE `{$this->table_name}` (
				`created_at`          timestamp               NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`updated_at`          timestamp               NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`chain_id`            bigint(20)    unsigned  NOT NULL,
				`contract_address`    varchar(191)            NOT NULL,
				PRIMARY KEY (`chain_id`)
			) ${charset};
		SQL;

		$result = $this->mysqli->query( $sql );
		assert( true === $result );
	}

	/**
	 * アプリケーションコントラクトテーブルを削除します。
	 */
	public function drop(): void {
		$sql = <<<SQL
			DROP TABLE IF EXISTS `{$this->table_name}`;
		SQL;

		$result = $this->mysqli->query( $sql );
		assert( true === $result );
	}

	/**
	 *
	 * @return AppContractType[]
	 */
	public function all(): array {
		$sql = <<<SQL
			SELECT * FROM `{$this->table_name}`
		SQL;

		$sql     = $this->wpdb->prepare( $sql );
		$results = array_map(
			fn( $result ) => AppContractType::from( (int) $result->chain_id, (string) $result->contract_address ),
			$this->wpdb->get_results( $sql )
		);

		return $results;
	}

	/**
	 *
	 * @param null|int    $chain_ID
	 * @param null|string $address
	 * @return AppContractType[]
	 */
	public function select( ?int $chain_ID = null, ?string $address = null ): array {
		$all_app_contracts = $this->all();
		if ( ! is_null( $chain_ID ) ) {
			$app_contract = array_filter( $all_app_contracts, fn( $app_contract ) => $app_contract->chainID() === $chain_ID );
		}
		if ( ! is_null( $address ) ) {
			$app_contract = array_filter( $app_contract, fn( $app_contract ) => $app_contract->contractAddress() === $address );
		}

		return array_values( $app_contract );
	}

	/**
	 * テーブルにアプリケーションコントラクトを追加します。
	 */
	public function insert( int $chain_id, string $contract_address ): void {
		$sql = <<<SQL
			INSERT INTO `{$this->table_name}` (`chain_id`, `contract_address`) VALUES (%d, %s)
		SQL;

		$sql    = $this->wpdb->prepare( $sql, $chain_id, $contract_address );
		$result = $this->wpdb->query( $sql );
		if ( false === $result ) {
			throw new \Exception( '[C6A4AD18] Failed to add contract data.' );
		}
	}
}
