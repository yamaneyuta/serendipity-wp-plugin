<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\Migration;

use Cornix\Serendipity\Core\Features\Migration\Version\MigrationBase;
use Cornix\Serendipity\Core\Lib\Repository\DBSchemaVersion;
use Cornix\Serendipity\Core\Lib\Algorithm\Sort\VersionSorter;
use Cornix\Serendipity\Core\Lib\Repository\Database\TableName;
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
		$db_schema_version = new DBSchemaVersion();
		$current_version   = $db_schema_version->get();

		// 現在のデータベースバージョンよりも大きいマイグレーションクラス名一覧をバージョンの小さい順に取得(`vX_X_X`形式)
		$migrate_classes = ( new MigrationClasses() )->get( '>', $current_version, 'ASC' );

		foreach ( $migrate_classes as $migrate_class ) {
			/** @var MigrationBase $migrator */
			$migrator = new $migrate_class( $this->wpdb );
			// マイグレーションを実行
			try {
				$migrator->up();
				// マイグレーション成功時はスキーマのバージョンを更新
				$version = MigrationClasses::classNameToVersion( $migrate_class );
				$db_schema_version->set( $version );

				assert( $db_schema_version->get() === $version );

			} catch ( \Exception $e ) {
				// マイグレーションに失敗した場合はロールバックを試みて例外を再スロー
				$migrator->down();
				throw $e;
			}
		}
	}

	public function rollback(): void {

		// 現時点では特定のバージョンに戻す機能は未実装。
		// ひとまず、各マイグレーションクラスのdownメソッドを呼び出してエラーが発生しないことを確認するためだけの実装。

		// MySQL(MariaDB)のサポートなのでROLLBACKはできない。よってBEGIN TRANSACTIONは不要。
		assert( $this->wpdb->is_mysql );

		$db_schema_version = new DBSchemaVersion();
		$current_version   = $db_schema_version->get();

		// 現在のデータベースバージョン以下のマイグレーションクラス名一覧をバージョンの大きい順に取得(`vX_X_X`形式)
		$rollback_classes = ( new MigrationClasses() )->get( '<=', $current_version, 'DESC' );

		foreach ( $rollback_classes as $i => $rollback_class ) {
			/** @var MigrationBase $migrator */
			$migrator = new $rollback_class( $this->wpdb );
			// ロールバックを実行
			$migrator->down();

			// ロールバック成功時はスキーマのバージョンを更新
			if ( $i === count( $rollback_classes ) - 1 ) {
				// 配列の最後の要素の場合、次にロールバックするクラスがないためバージョンを0.0.0に戻す
				$version = '0.0.0';
			} else {
				$version = MigrationClasses::classNameToVersion( $rollback_class[ $i + 1 ] );
			}
			$db_schema_version->set( $version );
			assert( $db_schema_version->get() === $version );
		}
	}

	public function uninstall(): void {
		// 削除対象のテーブル名一覧
		$drop_table_names = array(
			TableName::postSettingHistory(),
			// ※ 本プラグインで扱うテーブルが増えた場合はここに追加。
		);

		$drop_tables = implode( ',', array_map( fn( $table_name ) => "`$table_name`", $drop_table_names ) ); // 削除対象のテーブル名をカンマで連結

		// テーブルを削除(mysqliを使用)
		$mysqli = ( new MySQLiFactory() )->create( $this->wpdb );
		$mysqli->query( "DROP TABLE IF EXISTS $drop_tables;" );
	}
}


class MigrationClasses {

	public function get( string $operator, string $version, string $order = 'ASC' ): array {
		// $versionは`X.X.X`の形式
		assert( strpos( $version, '.' ) !== false && strpos( $version, '_' ) === false );

		// Migrationsディレクトリ内のファイル名からバージョンを取得
		$files       = glob( __DIR__ . '/Version/v*_*.php' );
		$class_names = array_map( fn( $file ) => basename( $file, '.php' ), $files ); // ファイル名＝クラス名
		$versions    = array_map( fn( $base_name ) => self::classNameToVersion( $base_name ), $class_names );

		// 現在のバージョンよりも大きいバージョンを取得
		$versions = array_filter( $versions, fn( $ver ) => version_compare( $ver, $version, $operator ) );

		// 指定された並び順にソート
		$versions = ( new VersionSorter() )->sort( $versions, $order );

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
		return __NAMESPACE__ . '\\Version\\' . $class_name;
	}
}
