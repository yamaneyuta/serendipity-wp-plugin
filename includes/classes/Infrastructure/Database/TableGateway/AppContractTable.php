<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\TableGateway;

use Cornix\Serendipity\Core\Domain\Entity\AppContract;
use Cornix\Serendipity\Core\Infrastructure\Database\ValueObject\AppContractTableRecord;
use Cornix\Serendipity\Core\Repository\Name\TableName;

/**
 * Appコントラクトの情報を記録するテーブル
 */
class AppContractTable extends TableBase {

	public function __construct( \wpdb $wpdb ) {
		parent::__construct( $wpdb, ( new TableName() )->appContract() );
	}

	/**
	 * @return AppContractTableRecord[]
	 */
	public function all(): array {
		$sql     = <<<SQL
			SELECT `chain_id`, `address`, `activation_block_number`, `crawled_block_number`
			FROM `{$this->tableName()}`
		SQL;
		$results = $this->wpdb()->get_results( $sql );

		if ( ! is_array( $results ) ) {
			throw new \Exception( '[0C248CD9] Failed to fetch app contract records. ' . $this->wpdb()->last_error );
		}

		return array_map(
			function ( $record ) {
				// 型をテーブル定義に一致させる
				$record->chain_id                = (int) $record->chain_id;
				$record->activation_block_number = (int) $record->activation_block_number;
				$record->crawled_block_number    = (int) $record->crawled_block_number;

				// AppContractTableRecordのインスタンスを返す
				return new AppContractTableRecord( $record );
			},
			(array) $results
		);
	}

	public function save( AppContract $app_contract ): void {
		$sql = <<<SQL
			INSERT INTO `{$this->tableName()}`
				(`chain_id`, `address`, `activation_block_number`, `crawled_block_number`)
			VALUES
				(:chain_id, :address, :activation_block_number, :crawled_block_number)
			ON DUPLICATE KEY UPDATE
				`address` = VALUES(`address`),
				`activation_block_number` = VALUES(`activation_block_number`),
				`crawled_block_number` = VALUES(`crawled_block_number`)
		SQL;
		$sql = $this->namedPrepare(
			$sql,
			array(
				':chain_id'                => $app_contract->chain()->id(),
				':address'                 => $app_contract->address()->value(),
				':activation_block_number' => $app_contract->activationBlockNumber(),
				':crawled_block_number'    => $app_contract->crawledBlockNumber(),
			)
		);

		$result = $this->wpdb()->query( $sql );
		if ( false === $result ) {
			throw new \Exception( '[1AA48899] Failed to insert or update chain data. ' . $this->wpdb()->last_error );
		}
	}
}
