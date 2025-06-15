<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\TableMigration\versions\_0_0_1;

use Cornix\Serendipity\Core\Constant\ChainIdValue;
use Cornix\Serendipity\Core\Infrastructure\Database\TableMigration\DatabaseMigrationBase;
use Cornix\Serendipity\Core\Infrastructure\Web3\Ethers;
use Cornix\Serendipity\Core\Repository\Name\TableName;

return new class() extends DatabaseMigrationBase {

	public function __construct() {
		$this->table_name = ( new TableName() )->token();
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
			throw new \RuntimeException( '[E016C899] Error: ' . $this->mysqli()->error );
		}
	}

	private function createTable(): void {

		$charset = $this->wpdb()->get_charset_collate();

		// - 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		$sql = <<<SQL
			CREATE TABLE `{$this->table_name}` (
				`created_at`     timestamp               NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`updated_at`     timestamp               NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`chain_id`       bigint        unsigned  NOT NULL,
				`address`        varchar(191)            NOT NULL,
				`symbol`         varchar(191)            NOT NULL,
				`decimals`       int                     NOT NULL,
				`is_payable`     boolean                 NOT NULL,
				PRIMARY KEY (`chain_id`, `address`)
			) {$charset};
		SQL;

		$result = $this->mysqli()->query( $sql );
		if ( true !== $result ) {
			throw new \RuntimeException( '[888C1092] Error: ' . $this->mysqli()->error );
		}
	}

	private function insertInitialData(): void {
		$zero_address = Ethers::zeroAddress()->value();
		$Record       = new class( 0, '', '', 0, false ) {
			public function __construct(
				int $chain_id,
				string $address,
				string $symbol,
				int $decimals,
				bool $is_payable
			) {
				$this->chain_id   = $chain_id;
				$this->address    = $address;
				$this->symbol     = $symbol;
				$this->decimals   = $decimals;
				$this->is_payable = $is_payable;
			}
			public int $chain_id;
			public string $address;
			public string $symbol;
			public int $decimals;
			public bool $is_payable;
		};

		$records = array();

		// メインネットのネイティブトークンを登録(Ethereum Mainnetのみ支払可能として指定)
		$records[] = new $Record( ChainIdValue::ETH_MAINNET, $zero_address, 'ETH', 18, true );

		// テストネットのネイティブトークンを登録(Sepoliaのみ支払可能として指定)
		$records[] = new $Record( ChainIdValue::SEPOLIA, $zero_address, 'ETH', 18, true );
		$records[] = new $Record( ChainIdValue::SONEIUM_MINATO, $zero_address, 'ETH', 18, false );

		// 開発モード時はプライベートネットのネイティブトークンを登録
		if ( $this->environment()->isDevelopmentMode() ) {
			$records[] = new $Record( ChainIdValue::PRIVATENET_L1, $zero_address, 'ETH', 18, true );
			$records[] = new $Record( ChainIdValue::PRIVATENET_L2, $zero_address, 'MATIC', 18, true );
		}

		foreach ( $records as $record ) {
			$result = $this->wpdb()->insert(
				$this->table_name,
				array(
					'chain_id'   => $record->chain_id,
					'address'    => $record->address,
					'symbol'     => $record->symbol,
					'decimals'   => $record->decimals,
					'is_payable' => $record->is_payable,
				)
			);
			if ( 1 !== $result ) {
				throw new \RuntimeException( '[32A2C355] result: ' . $result . ', Error: ' . $this->wpdb()->last_error );
			}
		}
	}
};
