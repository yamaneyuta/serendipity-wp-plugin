<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\Repository\Database;

use Cornix\Serendipity\Core\Features\Repository\Database\MigrationBase;
use Cornix\Serendipity\Core\Lib\Algorithm\Sort\VersionSorter;
use Cornix\Serendipity\Core\Lib\Repository\Database\TableName;
use Cornix\Serendipity\Core\Lib\Repository\Option\Option;
use mysqli;
use wpdb;

class DBSchema {
	public function __construct( wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	private wpdb $wpdb;

	public function migrate(): void {
		// MySQL(MariaDB)のサポートなのでROLLBACKはできない。よってBEGIN TRANSACTIONは不要。
		assert( $this->wpdb->is_mysql );

		// 現在のデータベースバージョンを取得(`X.X.X`形式)
		$option          = new Option();
		$current_version = $option->getDBSchemaVersion();

		// 現在のデータベースバージョンよりも大きいマイグレーションクラス名一覧を取得(`vX_X_X`形式)
		$migrate_classes = ( new MigrationClasses( $current_version ) )->get();

		foreach ( $migrate_classes as $migrate_class ) {
			/** @var MigrationBase $migrator */
			$migrator = new $migrate_class( $this->wpdb );
			// マイグレーションを実行
			try {
				$migrator->up();
				// マイグレーション成功時はスキーマのバージョンを更新
				$version = MigrationClasses::classNameToVersion( $migrate_class );
				$option->setDBSchemaVersion( $version );

				assert( $option->getDBSchemaVersion() === $version );

			} catch ( \Exception $e ) {
				// マイグレーションに失敗した場合はロールバックを試みて例外を再スロー
				$migrator->down();
				throw $e;
			}
		}
	}

	public function rollback(): void {
		// MySQL(MariaDB)のサポートなのでROLLBACKはできない。よってBEGIN TRANSACTIONは不要。
		assert( $this->wpdb->is_mysql );

		// Your own implementation.
	}

	public function uninstall(): void {
		// 削除対象のテーブル名一覧
		$drop_table_names = array(
			TableName::postSettingHistory(),
			// ※ 本プラグインで扱うテーブルが増えた場合はここに追加。
		);

		$drop_tables = implode( ',', array_map( fn( $table_name ) => "`$table_name`", $drop_table_names ) ); // 削除対象のテーブル名をカンマで連結

		// テーブルを削除(mysqliを使用)
		$mysqli = new mysqli( $this->wpdb->dbhost, $this->wpdb->dbuser, $this->wpdb->dbpassword, $this->wpdb->dbname );
		$mysqli->query( "DROP TABLE IF EXISTS $drop_tables;" );
	}

	private function stepMigrate( string $current_version ): string {
		throw new \Exception( 'TODO' );
	}
}


class MigrationClasses {
	public function __construct( string $currentVersion ) {
		// $currentVersionは`X.X.X`の形式
		assert( strpos( $currentVersion, '.' ) !== false );
		assert( strpos( $currentVersion, '_' ) === false );

		$this->currentVersion = $currentVersion;
	}

	private string $currentVersion;

	public function get(): array {

		// Migrationsディレクトリ内のファイル名からバージョンを取得
		$files       = glob( __DIR__ . '/Migrations/v*_*.php' );
		$class_names = array_map( fn( $file ) => basename( $file, '.php' ), $files ); // ファイル名＝クラス名
		$versions    = array_map( fn( $base_name ) => self::classNameToVersion( $base_name ), $class_names );

		// 現在のバージョンよりも大きいバージョンを取得
		$versions = array_filter( $versions, fn( $version ) => version_compare( $version, $this->currentVersion, '>' ) );
		// 小さい順にソート
		$versions = ( new VersionSorter() )->sort( $versions );

		// 名前空間を含むクラス名に変換して返す
		return array_map( fn( $version ) => $this->versionToClass( $version ), $versions );
	}

	public static function classNameToVersion( string $class_name ): string {
		// クラス名に名前空間が入っている場合は削除
		if ( strpos( $class_name, '\\' ) !== false ) {
			$class_name = substr( $class_name, strrpos( $class_name, '\\' ) + 1 );
		}

		// クラス名のフォーマット(`vX_X_X`形式)チェック。
		// - 最初の文字は`v`
		// - アンダーバーが含まれている
		// - ドットは含まれない
		assert( strpos( $class_name, 'v' ) === 0 );
		assert( strpos( $class_name, '_' ) !== false );
		assert( strpos( $class_name, '.' ) === false );

		// `v`を削除し、アンダーバーをドットに変換したもの(`X.X.X`形式)を返す
		return str_replace( '_', '.', substr( $class_name, 1 ) );
	}

	private function versionToClass( string $version ) {
		$class_name = 'v' . str_replace( '.', '_', $version );
		return __NAMESPACE__ . '\\Migrations\\' . $class_name;
	}
}
