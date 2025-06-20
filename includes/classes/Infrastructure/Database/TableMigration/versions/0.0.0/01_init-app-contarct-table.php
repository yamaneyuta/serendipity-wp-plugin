<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\TableMigration\versions\_0_0_1;

use Cornix\Serendipity\Core\Constant\ChainIdValue;
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
		$this->withTransaction( fn() => $this->insertInitialData() );
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
				`created_at`                       timestamp               NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`updated_at`                       timestamp               NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`chain_id`                         bigint        unsigned  NOT NULL,
				`address`                          varchar(191)            NOT NULL,
				`crawled_block_number`             bigint        unsigned,
				`crawled_block_number_updated_at`  timestamp,
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
			$insert_data[ ChainIdValue::PRIVATENET_L1 ]  = '0x5FbDB2315678afecb367f032d93F642f64180aa3';
			$insert_data[ ChainIdValue::PRIVATENET_L2 ]  = '0xe7f1725E7734CE288F8367e1Bb143E90bb3F0512';
			$insert_data[ ChainIdValue::SEPOLIA ]        = '0x6e98081f56608E3a9414823239f65c0e6399561d';
			$insert_data[ ChainIdValue::SONEIUM_MINATO ] = '0x6a9214D8264C00d884225542d3af47cf5De2049f';
		}

		foreach ( $insert_data as $chain_id => $address ) {
			$result = $this->wpdb()->insert(
				$this->table_name,
				array(
					'chain_id'                        => $chain_id,
					'address'                         => $address,
					'crawled_block_number'            => null,
					'crawled_block_number_updated_at' => null,
				)
			);
			if ( 1 !== $result ) {
				throw new \RuntimeException( '[F4A1B2C3] result: ' . $result . ', Error: ' . $this->wpdb()->last_error );
			}
		}
	}
};
