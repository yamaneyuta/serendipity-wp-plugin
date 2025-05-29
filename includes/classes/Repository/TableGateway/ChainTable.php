<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository\TableGateway;

use Cornix\Serendipity\Core\Constants\Config;
use Cornix\Serendipity\Core\Lib\Database\MySQLiFactory;
use Cornix\Serendipity\Core\Lib\Security\Judge;
use Cornix\Serendipity\Core\Repository\Name\TableName;

/**
 * チェーンの情報を記録するテーブル
 */
class ChainTable {

	public function __construct( \wpdb $wpdb = null ) {
		$this->wpdb       = $wpdb ?? $GLOBALS['wpdb'];
		$this->mysqli     = ( new MySQLiFactory() )->create( $this->wpdb );
		$this->table_name = ( new TableName() )->chain();
	}

	private \wpdb $wpdb;
	private \mysqli $mysqli;
	private string $table_name;

	/**
	 * テーブルを作成します。
	 */
	public function create(): void {
		$charset = $this->wpdb->get_charset_collate();

		// - 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		// - `confirmations`は将来的に`latest`のような文字列が入る可能性があるため、`varchar(191)`とする
		$sql = <<<SQL
			CREATE TABLE `{$this->table_name}` (
				`created_at`                   timestamp               NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`updated_at`                   timestamp               NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`chain_id`                     bigint        unsigned  NOT NULL,
				`name`                         varchar(191)            NOT NULL,
				`rpc_url`                      varchar(191),
				`confirmations`                varchar(191)            NOT NULL,
				PRIMARY KEY (`chain_id`)
			) {$charset};
		SQL;

		$result = $this->mysqli->query( $sql );
		assert( true === $result );
	}

	/**
	 * @return object{
	 *   chain_id: int,
	 *   name: string,
	 *   rpc_url: null|string,
	 *   confirmations: int|string
	 * }[]
	 */
	public function select( ?int $chain_ID = null ): array {
		$sql = <<<SQL
			SELECT `chain_id`, `name`, `rpc_url`, `confirmations`
			FROM `{$this->table_name}`
		SQL;

		// 条件がある場合はWHERE句を追加
		$wheres = array();
		if ( ! is_null( $chain_ID ) ) {
			$wheres[] = $this->wpdb->prepare( '`chain_id` = %d', $chain_ID );
		}

		if ( ! empty( $wheres ) ) {
			$sql .= ' WHERE ' . implode( ' AND ', $wheres );
		}

		$result = $this->wpdb->get_results( $sql );
		assert( is_array( $result ), '[583DBBE7] Invalid result type. Expected array, got ' . gettype( $result ) );

		foreach ( $result as $row ) {
			$row->chain_id      = (int) $row->chain_id;
			$row->confirmations = is_numeric( $row->confirmations )
				? (int) $row->confirmations
				: $row->confirmations;
		}

		return $result;
	}

	/**
	 * チェーン情報を新規作成します
	 *
	 * @param int    $chain_ID
	 * @param string $name
	 */
	public function insert( int $chain_ID, string $name ) {
		Judge::checkChainID( $chain_ID );

		$this->wpdb->insert(
			$this->table_name,
			array(
				'chain_id'      => $chain_ID,
				'name'          => $name,
				'rpc_url'       => null, // 初期値はnull
				'confirmations' => (string) Config::MIN_CONFIRMATIONS, // 初期値は最小待機ブロック数
			),
			array( '%d', '%s', '%s', '%s', '%s' )
		);
		if ( $this->wpdb->last_error ) {
			throw new \Exception( '[E8B777B8] Failed to insert or update chain data. ' . $this->wpdb->last_error );
		}
	}

	/** RPC URLを更新します。 */
	public function updateRpcURL( int $chain_ID, ?string $rpc_url ): void {
		Judge::checkChainID( $chain_ID );
		( ! is_null( $rpc_url ) ) && Judge::checkURL( $rpc_url );

		$result = $this->wpdb->update(
			$this->table_name,                  // table
			array( 'rpc_url' => $rpc_url ),     // data
			array( 'chain_id' => $chain_ID ),   // where
			array( '%s' ),                      // format
			array( '%d' )                       // where_format
		);

		if ( 1 < $result ) {
			throw new \Exception( '[8314C8C0] Failed to update RPC URL. result: ' . var_export( $result, true ) );
		}
		if ( $this->wpdb->last_error ) {
			throw new \Exception( '[BD9BA6FD] Failed to update RPC URL. ' . $this->wpdb->last_error );
		}
	}

	/**
	 * 指定されたチェーンIDの待機ブロック数を更新します。
	 *
	 * @param int        $chain_ID
	 * @param int|string $confirmations
	 */
	public function updateConfirmations( int $chain_ID, $confirmations ): void {
		Judge::checkChainID( $chain_ID );
		Judge::checkConfirmations( $confirmations );

		// confirmationsがint型の場合は文字列に変換
		$confirmations = is_int( $confirmations ) ? (string) $confirmations : $confirmations;

		$result = $this->wpdb->update(
			$this->table_name,                  // table
			array( 'confirmations' => $confirmations ), // data
			array( 'chain_id' => $chain_ID ),   // where
			array( '%s' ),                      // format
			array( '%d' )                       // where_format
		);

		if ( 1 < $result ) {
			throw new \Exception( '[7B341BB3] Failed to update confirmations. result: ' . var_export( $result, true ) );
		}
		if ( $this->wpdb->last_error ) {
			throw new \Exception( '[584805B9] Failed to update confirmations. ' . $this->wpdb->last_error );
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
