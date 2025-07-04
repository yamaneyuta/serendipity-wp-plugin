<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

use Cornix\Serendipity\Core\Infrastructure\Factory\AppContractRepositoryFactory;
use Cornix\Serendipity\Core\Repository\Name\TableName;
use Cornix\Serendipity\Core\Entity\SalesHistory;
use Cornix\Serendipity\Core\Infrastructure\Factory\ChainServiceFactory;
use wpdb;

/**
 * 売上データを取得するクラス
 */
class SalesHistoryService {
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
	 * @return SalesHistory[]
	 */
	public function select( ?string $invoice_id = null ): array {
		( new AppContractTmpTable( $this->wpdb ) )->create();
		$app_contract_table_name = ( new AppContractTmpTable( $this->wpdb ) )->tableName();

		$sql = <<<SQL
			SELECT
				inv.id AS invoice_id,
				inv.post_id,
				inv.chain_id,
				inv.selling_amount,
				inv.selling_decimals,
				inv.selling_symbol,
				inv.seller_address,
				inv.payment_amount,
				inv.consumer_address,
				tx.created_at,
				tx.block_number,
				tx.transaction_hash,
				(
					SELECT amount FROM `{$this->transfer_event_table_name}`
					WHERE invoice_id = inv.id AND to_address = inv.seller_address
				) as seller_profit_amount,
				(
					SELECT amount FROM `{$this->transfer_event_table_name}`
					WHERE invoice_id = inv.id AND EXISTS (
						SELECT 1
						FROM `{$app_contract_table_name}` AS app
						WHERE chain_id = inv.chain_id AND to_address = app.address
					)
				) as handling_fee_amount,
				tk.symbol as token_symbol,
				tk.address as token_address,
				tk.decimals as token_decimals
			FROM `{$this->invoice_table_name}` AS inv
			INNER JOIN `{$this->transaction_table_name}` AS tx ON inv.id = tx.invoice_id
			LEFT JOIN `{$this->token_table_name}` AS tk ON inv.chain_id = tk.chain_id AND inv.payment_token_address = tk.address
		SQL;

		if ( ! is_null( $invoice_id ) ) {
			$sql .= ' WHERE inv.id = %s';
			$sql  = $this->wpdb->prepare( $sql, $invoice_id );
		}

		$records = $this->wpdb->get_results( $sql, ARRAY_A );

		return array_map(
			fn( $record ) => SalesHistory::fromRecord( $record ),
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
				`chain_id`  bigint        unsigned  NOT NULL,
				`address`   varchar(191)            NOT NULL,
				PRIMARY KEY (`chain_id`),
				KEY `idx_{$table_name}_92D7958C` (`address`)
			) {$charset}
		SQL
		);

		// テーブルにデータを挿入
		$chains = ( new ChainServiceFactory() )->create()->getAllChains();
		foreach ( $chains as $chain ) {
			$app_contract = ( new AppContractRepositoryFactory() )->create()->get( $chain->id() );
			if ( is_null( $app_contract ) ) {
				continue;   // アプリケーションコントラクトが取得できないチェーンはスキップ
			}
			$sql    = <<<SQL
				INSERT INTO `{$table_name}` (`chain_id`, `address`)
				VALUES (%d, %s)
			SQL;
			$sql    = $this->wpdb->prepare( $sql, $chain->id()->value(), $app_contract->address()->value() );
			$result = $this->wpdb->query( $sql );
			assert( 1 === $result, "[5549D888] Failed to insert app contract address for chain ID {$chain->id()}" );
		}
	}

	public function tableName(): string {
		return 'tmp_app_contract';
	}
}
