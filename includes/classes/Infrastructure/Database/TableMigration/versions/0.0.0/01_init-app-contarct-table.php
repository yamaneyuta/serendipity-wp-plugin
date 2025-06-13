<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\TableMigration\versions\_0_0_1;

use Cornix\Serendipity\Core\Constant\ChainID;
use Cornix\Serendipity\Core\Infrastructure\Database\TableMigration\DatabaseMigrationBase;
use Cornix\Serendipity\Core\Repository\Name\TableName;

return new class() extends DatabaseMigrationBase {

	public function __construct() {
		$this->table_name = ( new TableName() )->appContract();
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
			throw new \RuntimeException( '[CC69F4C7] Error: ' . $this->mysqli()->error );
		}
	}

	private function createTable(): void {

		$charset = $this->wpdb()->get_charset_collate();

		// - 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		$sql = <<<SQL
			CREATE TABLE `{$this->table_name}` (
				`created_at`               timestamp               NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`updated_at`               timestamp               NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`chain_id`                 bigint        unsigned  NOT NULL,
				`address`                  varchar(191)            NOT NULL,
				`activation_block_number`  bigint        unsigned,
				`crawled_block_number`     bigint        unsigned,
				PRIMARY KEY (`chain_id`)
			) {$charset};
		SQL;

		$result = $this->mysqli()->query( $sql );
		if ( true !== $result ) {
			throw new \RuntimeException( '[BAC2FC48] Error: ' . $this->mysqli()->error );
		}
	}

	private function insertInitialData(): void {
		// 本番環境(Mainnet/Testnet)の初期データ
		$insert_data = array(
			// TODO: ここに本番環境用のコントラクトアドレスを定義
		);

		// 開発モード時は開発用のコントラクトアドレスを使用(テストネットのアドレスは上書き)
		if ( $this->environment()->isDevelopmentMode() ) {
			$insert_data[ ChainID::PRIVATENET_L1 ]  = '0x5FbDB2315678afecb367f032d93F642f64180aa3';
			$insert_data[ ChainID::PRIVATENET_L2 ]  = '0x7FfC0B1d3b8c4e5A6a9E5D3F8b1B2c4E6F7F8D9E';
			$insert_data[ ChainID::SEPOLIA ]        = '0x6e98081f56608E3a9414823239f65c0e6399561d';
			$insert_data[ ChainID::SONEIUM_MINATO ] = '0x6a9214D8264C00d884225542d3af47cf5De2049f';
		}

		foreach ( $insert_data as $chain_id => $address ) {
			$result = $this->wpdb()->insert(
				$this->table_name,
				array(
					'chain_id'                => $chain_id,
					'address'                 => $address,
					'activation_block_number' => null,
					'crawled_block_number'    => null,
				)
			);
			if ( 1 !== $result ) {
				throw new \RuntimeException( '[F4A1B2C3] result: ' . $result . ', Error: ' . $this->wpdb()->last_error );
			}
		}
	}
};
