<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\TableMigration\versions\_0_0_1;

use Cornix\Serendipity\Core\Infrastructure\Database\TableMigration\DatabaseMigrationBase;
use Cornix\Serendipity\Core\Repository\Name\TableName;
use Cornix\Serendipity\Core\Application\Factory\ServerSignerServiceFactory;

return new class() extends DatabaseMigrationBase {

	public function __construct() {
		$this->table_name = ( new TableName() )->serverSigner();
	}
	private string $table_name;

	/** @inheritdoc */
	public function up(): void {
		if ( ! $this->isTableExists( $this->table_name ) ) {
			// テーブルが存在しない場合はテーブルを作成し、署名用ウォレットデータを初期化
			$this->createTable();
			$this->initData();
		}

		assert( $this->isTableExists( $this->table_name ), '[EEC3C143]' );
	}

	/** @inheritdoc */
	public function down(): void {
		$sql    = "DROP TABLE IF EXISTS `{$this->table_name}`;";
		$result = $this->mysqli()->query( $sql );
		if ( true !== $result ) {
			throw new \RuntimeException( '[73DE1A50] Error: ' . $this->mysqli()->error );
		}
	}

	private function createTable(): void {
		$sql = <<<SQL
			CREATE TABLE `{$this->table_name}` (
				`created_at`        timestamp      NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`updated_at`        timestamp      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`address`           varchar(191)   NOT NULL,
				`private_key_data`  varchar(191)   NOT NULL,
				`encryption_key`    varchar(191),
				`encryption_iv`     varchar(191),
				PRIMARY KEY (`address`)
			) {$this->wpdb()->get_charset_collate()};
		SQL;

		$result = $this->mysqli()->query( $sql );
		if ( true !== $result ) {
			throw new \RuntimeException( '[EC96B939] Error: ' . $this->mysqli()->error );
		}
	}

	private function initData(): void {
		// 念のためレコードが存在しないことを確認
		if ( 0 !== $this->tableRecordCount( $this->table_name ) ) {
			throw new \RuntimeException( '[FB57DCDE] Server signer table already has data. Cannot initialize.' );
		}

		$server_signer_data = ( new ServerSignerServiceFactory() )->create( $this->wpdb() )->generateServerSignerData();

		// テーブルにデータを挿入
		$result = $this->wpdb()->insert(
			$this->table_name,
			array(
				'address'          => $server_signer_data->address(),
				'private_key_data' => $server_signer_data->privateKeyData(),
				'encryption_key'   => $server_signer_data->encryptionKey(),
				'encryption_iv'    => $server_signer_data->encryptionIv(),
			)
		);
		if ( 1 !== $result ) {
			throw new \RuntimeException( '[1A146680] result: ' . $result . ', Error: ' . $this->wpdb()->last_error );
		}
	}
};
