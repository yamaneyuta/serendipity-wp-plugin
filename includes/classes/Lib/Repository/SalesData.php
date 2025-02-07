<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Definition\AppContractDefinition;
use Cornix\Serendipity\Core\Lib\Repository\Name\TableName;
use Cornix\Serendipity\Core\Types\SalesDataType;
use wpdb;

/**
 * 売上データを取得するクラス
 */
class SalesData {
	public function __construct( wpdb $wpdb = null ) {
		$this->wpdb = $wpdb ?? $GLOBALS['wpdb'];

		$table_name                      = new TableName();
		$this->token_table_name          = $table_name->token();
		$this->invoice_table_name        = $table_name->invoice();
		$this->transaction_table_name    = $table_name->unlockPaywallTransaction();
		$this->transfer_event_table_name = $table_name->unlockPaywallTransferEvent();
	}

	private wpdb $wpdb;
	private string $token_table_name;
	private string $invoice_table_name;
	private string $transaction_table_name;
	private string $transfer_event_table_name;

	/**
	 *
	 * @return SalesDataType[]
	 */
	public function select(): array {
		( new AppContractTmpTable( $this->wpdb ) )->create();
		$app_contract_table_name = ( new AppContractTmpTable( $this->wpdb ) )->tableName();

		$sql = <<<SQL
			SELECT
				inv.id AS invoice_id,
				inv.post_id,
				inv.chain_id,
				inv.selling_amount_hex,
				inv.selling_decimals,
				inv.selling_symbol,
				inv.seller_address,
				inv.payment_amount_hex,
				inv.consumer_address,
				tx.created_at,
				tx.block_number,
				tx.transaction_hash,
				(
					SELECT amount_hex FROM `{$this->transfer_event_table_name}`
					WHERE invoice_id = inv.id AND to_address = inv.seller_address
				) as seller_profit_amount_hex,
				(
					SELECT amount_hex FROM `{$this->transfer_event_table_name}`
					WHERE invoice_id = inv.id AND EXISTS (
						SELECT 1
						FROM `{$app_contract_table_name}` AS app
						WHERE to_address = app.address
					)
				) as handling_fee_amount_hex,
				tk.symbol as token_symbol,
				tk.token_address as token_address,
				tk.decimals as token_decimals
			FROM `{$this->invoice_table_name}` AS inv
			INNER JOIN `{$this->transaction_table_name}` AS tx ON inv.id = tx.invoice_id
			LEFT JOIN `{$this->token_table_name}` AS tk ON inv.chain_id = tk.chain_id AND inv.payment_token_address = tk.token_address
		SQL;

		$records = $this->wpdb->get_results( $sql, ARRAY_A );

		return array_map(
			fn( $record ) => SalesDataType::fromRecord( $record ),
			$records
		);
	}
}

class AppContractTmpTable {

	public function __construct( wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	private wpdb $wpdb;

	public function create(): void {
		$table_name = $this->tableName();

		// テーブルが存在する場合は削除
		$this->wpdb->query( "DROP TABLE IF EXISTS `{$table_name}`" );

		// テーブルを作成
		$charset = $this->wpdb->get_charset_collate();
		$this->wpdb->query(
			<<<SQL
			CREATE TEMPORARY TABLE `{$table_name}` (
				`chain_id`  bigint(20)    unsigned  NOT NULL,
				`address`   varchar(191)            NOT NULL,
				PRIMARY KEY (`chain_id`)
			) {$charset}
		SQL
		);

		// テーブルにデータを挿入
		$app_contracts = ( new AppContractDefinition() )->all();
		foreach ( $app_contracts as $app_contract ) {
			$this->wpdb->query(
				<<<SQL
				INSERT INTO `{$table_name}` (chain_id, address)
				VALUES ({$app_contract->chainID()}, '{$app_contract->address()}')
			SQL
			);
		}
	}

	public function tableName(): string {
		return 'tmp_app_contract';
	}
}
