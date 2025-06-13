<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\TableMigration;

use Cornix\Serendipity\Core\Infrastructure\Database\MySQLiFactory;
use Cornix\Serendipity\Core\Repository\Environment;
use mysqli;
use wpdb;

abstract class DatabaseMigrationBase {

	private wpdb $wpdb;
	private mysqli $mysqli;
	private Environment $environment;

	public function setWpdb( wpdb $wpdb ): void {
		$this->wpdb   = $wpdb;
		$this->mysqli = ( new MySQLiFactory() )->create( $this->wpdb );
	}

	protected function wpdb(): wpdb {
		if ( ! isset( $this->wpdb ) ) {
			throw new \RuntimeException( '[514D07D1] wpdb is not set. Please call setWpdb() before using this method.' );
		}
		return $this->wpdb;
	}

	public function setEnvironment( Environment $environment ): void {
		$this->environment = $environment;
	}

	protected function environment(): Environment {
		if ( ! isset( $this->environment ) ) {
			throw new \RuntimeException( '[5D64706C] Environment is not set. Please call setEnvironment() before using this method.' );
		}
		return $this->environment;
	}

	protected function mysqli(): mysqli {
		if ( ! isset( $this->mysqli ) ) {
			throw new \RuntimeException( '[1F28CC56] mysqli is not set. Please call setWpdb() before using this method.' );
		}
		return $this->mysqli;
	}

	/** テーブルのレコード数を取得します */
	protected function tableRecordCount( string $table_name ): int {
		$sql    = "SELECT COUNT(*) FROM `{$table_name}`";
		$result = $this->wpdb()->get_var( $sql );
		if ( null === $result ) {
			throw new \RuntimeException( "[3866759A] Failed to count records in table: {$table_name}. " . $this->wpdb()->last_error );
		} elseif ( ! is_numeric( $result ) ) {
			throw new \RuntimeException( "[4A69B0DF] Count result is not numeric for table: {$table_name}. Result: {$result}" );
		}
		return (int) $result;
	}

	/** テーブルが存在するかどうかを取得します */
	protected function isTableExists( string $table_name ): bool {
		$sql     = "SHOW TABLES LIKE '{$table_name}'";
		$results = $this->wpdb()->get_results( $sql );
		if ( false === $results ) {
			throw new \RuntimeException( "[0C3F212E] Failed to check if table exists: {$table_name}. " . $this->wpdb()->last_error );
		} elseif ( count( $results ) > 1 ) {
			throw new \RuntimeException( "[AE32D3E1] More than one table found with the name: {$table_name}, count: " . count( $results ) );
		}
		assert( count( $results ) <= 1, '[75255AB4] count: ' . count( $results ) . ', table_name: ' . $table_name );

		return 1 === count( $results );
	}

	/** マイグレーションを適用します */
	abstract public function up(): void;

	/**
	 * マイグレーションを元に戻します
	 * ※ 完全に元に戻すことは保証されません。(データの削除や変更が行われた場合など)
	 */
	abstract public function down(): void;
}
