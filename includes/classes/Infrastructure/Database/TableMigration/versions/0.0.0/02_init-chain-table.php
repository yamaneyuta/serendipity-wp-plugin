<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\TableMigration\versions\_0_0_1;

use Cornix\Serendipity\Core\Constant\ChainIdValue;
use Cornix\Serendipity\Core\Constant\NetworkCategoryID;
use Cornix\Serendipity\Core\Infrastructure\Database\TableMigration\Config\InitialBlockExplorerURL;
use Cornix\Serendipity\Core\Infrastructure\Database\TableMigration\DatabaseMigrationBase;
use Cornix\Serendipity\Core\Repository\Name\TableName;

return new class() extends DatabaseMigrationBase {

	public function __construct() {
		$this->table_name = ( new TableName() )->chain();
	}
	private string $table_name;

	/** @inheritdoc */
	public function up(): void {
		// テーブルを作成
		$this->createTable();

		// 初期データを挿入
		$this->insertInitialData();
	}

	/** @inheritdoc */
	public function down(): void {
		$sql    = "DROP TABLE IF EXISTS `{$this->table_name}`;";
		$result = $this->mysqli()->query( $sql );
		if ( true !== $result ) {
			throw new \RuntimeException( '[123E2963] Error: ' . $this->mysqli()->error );
		}
	}

	private function createTable(): void {

		$charset = $this->wpdb()->get_charset_collate();

		// - 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		// - `confirmations`は将来的に`latest`のような文字列が入る可能性があるため、`varchar(191)`とする
		$sql = <<<SQL
			CREATE TABLE `{$this->table_name}` (
				`created_at`                   timestamp               NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`updated_at`                   timestamp               NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`chain_id`                     bigint        unsigned  NOT NULL,
				`name`                         varchar(191)            NOT NULL,
				`network_category_id`          int           unsigned  NOT NULL,
				`rpc_url`                      varchar(191),
				`confirmations`                varchar(191)            NOT NULL,
				`block_explorer_url`           varchar(191),
				PRIMARY KEY (`chain_id`)
			) {$charset};
		SQL;

		$result = $this->mysqli()->query( $sql );
		if ( true !== $result ) {
			throw new \RuntimeException( '[FB0D1E30] Error: ' . $this->mysqli()->error );
		}
	}

	private function insertInitialData(): void {

		$Record = new class( 1, '', 1, null, '', '' ) {
			public function __construct(
				int $chain_id,
				string $name,
				int $network_category_id,
				?string $rpc_url,
				string $confirmations,
				?string $block_explorer_url
			) {
				$this->chain_id            = $chain_id;
				$this->name                = $name;
				$this->network_category_id = $network_category_id;
				$this->rpc_url             = $rpc_url;
				$this->confirmations       = $confirmations;
				$this->block_explorer_url  = $block_explorer_url;
			}
			public int $chain_id;
			public string $name;
			public int $network_category_id;
			public ?string $rpc_url;
			public string $confirmations;
			public ?string $block_explorer_url;
		};

		$records = array(
			new $Record( ChainIdValue::ETH_MAINNET, 'Ethereum Mainnet', NetworkCategoryID::MAINNET, null, '1', InitialBlockExplorerURL::ETH_MAINNET ),
			new $Record( ChainIdValue::SEPOLIA, 'Sepolia', NetworkCategoryID::TESTNET, null, '1', InitialBlockExplorerURL::SEPOLIA ),
			new $Record( ChainIdValue::SONEIUM_MINATO, 'Soneium Testnet Minato', NetworkCategoryID::TESTNET, null, '1', InitialBlockExplorerURL::SONEIUM_MINATO ),
		);
		// 開発モード時はプライベートネットのチェーン情報も登録
		if ( $this->environment()->isDevelopmentMode() ) {
			$records[] = new $Record( ChainIdValue::PRIVATENET_L1, 'Privatenet1', NetworkCategoryID::PRIVATENET, $this->getPrivatenetRpcURL( ChainIdValue::PRIVATENET_L1 ), '1', InitialBlockExplorerURL::PRIVATENET_L1 );
			$records[] = new $Record( ChainIdValue::PRIVATENET_L2, 'Privatenet2', NetworkCategoryID::PRIVATENET, $this->getPrivatenetRpcURL( ChainIdValue::PRIVATENET_L2 ), '1', InitialBlockExplorerURL::PRIVATENET_L2 );
		}

		foreach ( $records as $record ) {
			$result = $this->wpdb()->insert(
				$this->table_name,
				array(
					'chain_id'            => $record->chain_id,
					'name'                => $record->name,
					'network_category_id' => $record->network_category_id,
					'rpc_url'             => $record->rpc_url,
					'confirmations'       => $record->confirmations,
					'block_explorer_url'  => $record->block_explorer_url,
				)
			);
			if ( 1 !== $result ) {
				throw new \RuntimeException( '[D0746EC0] result: ' . $result . ', Error: ' . $this->wpdb()->last_error );
			}
		}
	}


	/**
	 * 指定されたチェーンIDに対応するプライベートネットのRPC URLを取得します。
	 *
	 * @param int $chain_ID
	 */
	private function getPrivatenetRpcURL( int $chain_ID ): ?string {

		// プライベートネットのURLを取得する関数
		$privatenet = function ( int $number ): string {
			assert( in_array( $number, array( 1, 2 ), true ) );
			$prefix = $this->environment()->isTesting() ? 'tests-' : '';
			return "http://{$prefix}privatenet-{$number}.local";
		};

		switch ( $chain_ID ) {
			case ChainIdValue::PRIVATENET_L1:
				return $privatenet( 1 );
			case ChainIdValue::PRIVATENET_L2:
				return $privatenet( 2 );
			default:
				throw new \InvalidArgumentException( '[9739363E] Invalid chain ID. ' . $chain_ID );
		}
	}
};
