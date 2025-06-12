<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\TableMigration\versions\_0_0_1;

use Cornix\Serendipity\Core\Constant\ChainID;
use Cornix\Serendipity\Core\Infrastructure\Database\TableMigration\DatabaseMigrationBase;
use Cornix\Serendipity\Core\Repository\Name\TableName;

return new class() extends DatabaseMigrationBase {

	public function __construct() {
		$this->table_name = ( new TableName() )->oracle();
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
			throw new \RuntimeException( '[CF7BEF96] Error: ' . $this->mysqli()->error );
		}
	}

	private function createTable(): void {

		$charset         = $this->wpdb()->get_charset_collate();
		$unique_key_name = "uq_{$this->table_name}_C269159C";

		// - 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
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

		$result = $this->mysqli()->query( $sql );
		if ( true !== $result ) {
			throw new \RuntimeException( '[55E13EF4] Error: ' . $this->mysqli()->error );
		}
	}

	private function insertInitialData(): void {
		$Record = new class( 0, '', '', '' ) {
			public function __construct(
				int $chain_id,
				string $address,
				string $base_symbol,
				string $quote_symbol
			) {
				$this->chain_id     = $chain_id;
				$this->address      = $address;
				$this->base_symbol  = $base_symbol;
				$this->quote_symbol = $quote_symbol;
			}
			public int $chain_id;
			public string $address;
			public string $base_symbol;
			public string $quote_symbol;
		};

		$records = array();

		// Oracleのアドレスは以下のURLで確認可能
		// https://docs.chain.link/data-feeds/price-feeds/addresses

		// ■ Fiat
		// 以下のコマンドでFiatのOracleアドレスを確認可能
		// curl -s https://reference-data-directory.vercel.app/feeds-mainnet.json | jq '.[] | select(.docs.assetClass == "Fiat")'
		if ( 'ja' === substr( get_locale(), 0, 2 ) || $this->environment()->isDevelopmentMode() ) {
			// サイトの言語が日本語の場合、もしくは開発モード時は、`JPY / USD`を登録
			$records[] = new $Record( ChainID::ETH_MAINNET, '0xBcE206caE7f0ec07b545EddE332A47C2F75bbeb3', 'JPY', 'USD' );
		}
		// ■ Crypto
		$records[] = new $Record( ChainID::ETH_MAINNET, '0x5f4eC3Df9cbd43714FE2740f5E3616155c5b8419', 'ETH', 'USD' );

		// テスト中はプライベートネットのOracleを登録
		if ( $this->environment()->isTesting() ) {
			// プライベートネットのOracleを登録
			$records[] = new $Record( ChainID::PRIVATENET_L1, '0x3F3B6a555F3a7DeD78241C787e0cDD8E431A64A8', 'ETH', 'USD' );
			$records[] = new $Record( ChainID::PRIVATENET_L1, '0xc886d2C1BEC5819b4B8F84f35A9885519869A8EE', 'JPY', 'USD' );
		}

		foreach ( $records as $record ) {
			$result = $this->wpdb()->insert(
				$this->table_name,
				array(
					'chain_id'     => $record->chain_id,
					'address'      => $record->address,
					'base_symbol'  => $record->base_symbol,
					'quote_symbol' => $record->quote_symbol,
				)
			);
			if ( 1 !== $result ) {
				throw new \RuntimeException( '[788B1842] result: ' . $result . ', Error: ' . $this->wpdb()->last_error );
			}
		}
	}
};
