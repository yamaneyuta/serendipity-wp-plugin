<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\TableGateway;

use Cornix\Serendipity\Core\Domain\Entity\Chain;
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
	 */
	public function all(): array {
		$sql     = <<<SQL
			SELECT `chain_id`, `name`, `network_category_id`, `rpc_url`, `confirmations`
			FROM `{$this->tableName()}`
		SQL;
		$results = $this->wpdb()->get_results( $sql );
		assert( is_array( $results ), '[583DBBE7] Invalid result type. Expected array, got ' . gettype( $results ) );

		return array_values(
			array_map(
				function ( $row ) {
					// 型をテーブル定義を一致させる
					$row->chain_id            = (int) $row->chain_id;
					$row->network_category_id = (int) $row->network_category_id;

					return new ChainTableRecord( $row );
				},
				$results
			)
		);
	}

	public function save( Chain $chain ): void {
		$sql = <<<SQL
			INSERT INTO `{$this->tableName()}`
				(`chain_id`, `name`, `network_category_id`, `rpc_url`, `confirmations`)
			VALUES
				(:chain_id, :name, :network_category_id, :rpc_url, :confirmations)
			ON DUPLICATE KEY UPDATE
				`name` = VALUES(`name`),
				`network_category_id` = VALUES(`network_category_id`),
				`rpc_url` = VALUES(`rpc_url`),
				`confirmations` = VALUES(`confirmations`)
		SQL;
		$sql = $this->namedPrepare(
			$sql,
			array(
				':chain_id'            => $chain->id()->value(),
				':name'                => $chain->name(),
				':network_category_id' => $chain->networkCategory()->id(),
				':rpc_url'             => $chain->rpcURL(),
				':confirmations'       => (string) $chain->confirmations(),
			)
		);

		$result = $this->wpdb()->query( $sql );
		if ( false === $result ) {
			throw new \Exception( '[E01C7DE3] Failed to insert or update chain data. ' . $this->wpdb()->last_error );
		}
	}
}
