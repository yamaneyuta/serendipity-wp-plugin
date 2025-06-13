<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\TableGateway;

use Cornix\Serendipity\Core\Constant\Config;
use Cornix\Serendipity\Core\Domain\Entity\Chain;
use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\Repository\Name\TableName;
use Cornix\Serendipity\Core\Infrastructure\Database\ValueObject\ChainTableRecord;

/**
 * チェーンの情報を記録するテーブル
 */
class ChainTable extends TableBase {

	public function __construct( \wpdb $wpdb ) {
		parent::__construct( $wpdb, ( new TableName() )->chain() );
	}

	/**
	 * @return ChainTableRecord[]
	 * TODO: 引数を削除
	 */
	public function select( ?int $chain_ID = null ): array {
		// レコード数は少ないのですべてのレコードを取得してからフィルタリングする
		$sql     = <<<SQL
			SELECT `chain_id`, `name`, `rpc_url`, `confirmations`
			FROM `{$this->tableName()}`
		SQL;
		$results = $this->wpdb()->get_results( $sql );
		assert( is_array( $results ), '[583DBBE7] Invalid result type. Expected array, got ' . gettype( $results ) );

		$chain_table_records = array_map(
			function ( $row ) {
				// 型をテーブル定義を一致させる
				$row->chain_id = (int) $row->chain_id;

				return new ChainTableRecord( $row );
			},
			$results
		);

		// チェーンIDでフィルタ
		if ( ! is_null( $chain_ID ) ) {
			$chain_table_records = array_filter(
				$chain_table_records,
				fn( $record ) => $record->chainID() === $chain_ID
			);
			assert( count( $chain_table_records ) <= 1, '[9A6ADAB1] should return at most one record.' );
		}

		return array_values( $chain_table_records );
	}

	public function save( Chain $chain ): void {
		$sql = <<<SQL
			INSERT INTO `{$this->tableName()}`
				(`chain_id`, `name`, `rpc_url`, `confirmations`)
			VALUES
				(:chain_id, :name, :rpc_url, :confirmations)
			ON DUPLICATE KEY UPDATE
				`name` = VALUES(`name`),
				`rpc_url` = VALUES(`rpc_url`),
				`confirmations` = VALUES(`confirmations`)
		SQL;
		$sql = $this->namedPrepare(
			$sql,
			array(
				':chain_id'      => $chain->id(),
				':name'          => $chain->name(),
				':rpc_url'       => $chain->rpcURL(),
				':confirmations' => (string) $chain->confirmations(),
			)
		);

		$result = $this->wpdb()->query( $sql );
		if ( false === $result ) {
			throw new \Exception( '[E01C7DE3] Failed to insert or update chain data. ' . $this->wpdb()->last_error );
		}
	}

	/**
	 * チェーン情報を新規作成します
	 *
	 * @param int    $chain_ID
	 * @param string $name
	 * @deprecated
	 */
	public function insert( int $chain_ID, string $name ) {
		Validate::checkChainID( $chain_ID );

		$this->wpdb()->insert(
			$this->tableName(),
			array(
				'chain_id'      => $chain_ID,
				'name'          => $name,
				'rpc_url'       => null, // 初期値はnull
				'confirmations' => (string) Config::MIN_CONFIRMATIONS, // 初期値は最小待機ブロック数
			),
			array( '%d', '%s', '%s', '%s', '%s' )
		);
		if ( $this->wpdb()->last_error ) {
			throw new \Exception( '[E8B777B8] Failed to insert or update chain data. ' . $this->wpdb()->last_error );
		}
	}

	/** @deprecated */
	public function updateRpcURL( int $chain_ID, ?string $rpc_url ): void {
		Validate::checkChainID( $chain_ID );
		( ! is_null( $rpc_url ) ) && Validate::checkURL( $rpc_url );

		$result = $this->wpdb()->update(
			$this->tableName(),                 // table
			array( 'rpc_url' => $rpc_url ),     // data
			array( 'chain_id' => $chain_ID ),   // where
			array( '%s' ),                      // format
			array( '%d' )                       // where_format
		);

		if ( 1 < $result ) {
			throw new \Exception( '[8314C8C0] Failed to update RPC URL. result: ' . var_export( $result, true ) );
		}
		if ( $this->wpdb()->last_error ) {
			throw new \Exception( '[BD9BA6FD] Failed to update RPC URL. ' . $this->wpdb()->last_error );
		}
	}

	/**
	 * 指定されたチェーンIDの待機ブロック数を更新します。
	 *
	 * @param int        $chain_ID
	 * @param int|string $confirmations
	 * @deprecated
	 */
	public function updateConfirmations( int $chain_ID, $confirmations ): void {
		Validate::checkChainID( $chain_ID );
		Validate::checkConfirmations( $confirmations );

		// confirmationsがint型の場合は文字列に変換
		$confirmations = is_int( $confirmations ) ? (string) $confirmations : $confirmations;

		$result = $this->wpdb()->update(
			$this->tableName(),                 // table
			array( 'confirmations' => $confirmations ), // data
			array( 'chain_id' => $chain_ID ),   // where
			array( '%s' ),                      // format
			array( '%d' )                       // where_format
		);

		if ( 1 < $result ) {
			throw new \Exception( '[7B341BB3] Failed to update confirmations. result: ' . var_export( $result, true ) );
		}
		if ( $this->wpdb()->last_error ) {
			throw new \Exception( '[584805B9] Failed to update confirmations. ' . $this->wpdb()->last_error );
		}
	}
}
