<?php

declare(strict_types=1);

namespace Cornix\Serendipity\Core\Database;

use Cornix\Serendipity\Core\Logger\Logger;
use Cornix\Serendipity\Core\Utils\Constants;

/**
 * データベースのスキーマを管理します。
 *
 * アップグレード用のファイルは、`assets/sql/schema/upgrade`にバージョン毎に配置。
 * 例えば、バージョン`1.0.0`から`1.0.1`にアップグレードする場合、フォルダ名は`1.0.0-1.0.1`とする。
 * フォルダ内のsqlファイルをファイル名の昇順で実行する。(順序が重要な場合は、ファイル名の先頭に数字を付ける等で対応する)
 *
 * - utf8mb4 は MySQL 5.5 以降でサポート。
 * - Innodb は MySQL 5.5 以降でデフォルトのエンジン。
 * - utf8mb4_unicode_520_ci は MySQL 5.6 以降で使用可能。
 * WordPress 4.0 の時点ですでに推奨が 5.6 以上なので全て指定する。
 */
class Schema {
	private const UPGRADE_SQL_DIR       = __DIR__ . '/../../assets/sql/schema/upgrade';
	private const DATABASE_VERSION_ZERO = '0.0.0';
	private const AUTO_LOAD_OPTION      = true;

	private function __construct() {
	}
	/** @var Schema */
	private static $_instance;

	private static function getInstance(): Schema {
		if ( ! isset( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	private function getVersionOptionKey(): string {
		return Constants::get( 'optionsKey.databaseVersion' );
	}

	private function getVersionFromOptionTable(): ?string {
		// optionsテーブルから値を取得して返す(取得できなかった場合は第二引数で指定したnullを返す)
		return get_option( $this->getVersionOptionKey(), null );
	}
	private function setVersionToOptionTable( string $database_version ): void {
		update_option( $this->getVersionOptionKey(), $database_version, self::AUTO_LOAD_OPTION );
	}
	private function removeVersionFromOptionTable(): void {
		delete_option( $this->getVersionOptionKey() );
	}

	/**
	 * データベースのアップグレードを行います。
	 */
	public static function upgrade(): void {
		$instance = self::getInstance();

		if ( ! file_exists( self::UPGRADE_SQL_DIR ) || ! is_dir( self::UPGRADE_SQL_DIR ) ) {
			Logger::error( 'self::UPGRADE_SQL_DIR: ' . self::UPGRADE_SQL_DIR );
			throw new \Exception( '{46E4E1AA-2981-4EB4-9A9A-E21890AC5459}' );
		}

		// optionsテーブルに保存されているデータベースのバージョンを取得(取得できなかった場合は`0.0.0`とする)
		$current_version = $instance->getVersionFromOptionTable();
		$current_version = $current_version ?? self::DATABASE_VERSION_ZERO;

		$upgrade_dir = $instance->get_upgrade_dir( $current_version );
		if ( is_null( $upgrade_dir ) ) {
			// アップグレード用のフォルダがない場合は何もしない
			return;
		}

		// ここで発行されるクエリはDDLなのでトランザクションを明示的に開始することはしない。
		// コメントアウト -> $GLOBALS['wpdb']->query( 'START TRANSACTION' );

		// 各フォルダ内のSQLファイルを実行
		while ( ! is_null( $upgrade_dir ) ) {
			// SQLを実行し、実行後のバージョンを取得
			$current_version = $instance->execute( $upgrade_dir );
			// 次のアップグレード用フォルダを取得
			$upgrade_dir = $instance->get_upgrade_dir( $current_version );
		}

		// データベースバージョンをoptionsテーブルに保存
		$instance->setVersionToOptionTable( $current_version );
	}

	/**
	 * 指定したバージョンからのアップグレードファイルが格納されているフォルダを取得します。
	 * アップグレード用のフォルダがない場合はfalseを返します。
	 */
	private function get_upgrade_dir( string $version ): ?string {
		// アップグレード対象のSQLフォルダの一覧を取得
		$files_or_dirs = scandir( self::UPGRADE_SQL_DIR );
		if ( false === $files_or_dirs ) {
			Logger::error( 'self::UPGRADE_SQL_DIR: ' . self::UPGRADE_SQL_DIR );
			throw new \Exception( '{729BB641-17E8-4FFE-9CC3-62A74F74C001}' );
		}

		foreach ( $files_or_dirs as $file_or_dir ) {
			// 対象がフォルダでない場合はスキップ
			if ( false === is_dir( self::UPGRADE_SQL_DIR . '/' . $file_or_dir ) ) {
				continue;
			}

			// フォルダ名をハイフンで分割し、前半が指定されたバージョンの時
			$version_from = explode( '-', $file_or_dir )[0];
			if ( $version_from === $version ) {
				// 戻り値(アップグレード用ファイルが格納されているフォルダ)を取得
				$result = self::UPGRADE_SQL_DIR . '/' . $file_or_dir;

				// 対象のフォルダ内のファイルを取得
				$sub_files = scandir( $result );
				if ( false === $sub_files ) {
					Logger::error( "result: $result" );
					throw new \Exception( '{CC47AC3C-F7E3-460F-9A44-16ADC1AE034E}' );
				}

				// 対象のフォルダ内に`.sql`ファイルが存在する場合は戻り値として保持してあった値を返す
				foreach ( $sub_files as $sub_file ) {
					if ( $this->is_sql_file( $sub_file ) ) {
						return $result;
					}
				}

				// 対象のフォルダ内に`*.sql`ファイルが存在しなかったので例外
				Logger::error( "result: $result" );
				throw new \Exception( '{E1D5229E-906D-4087-8353-0881793E4E1A}' );
			}
		}

		return null;
	}

	/**
	 * 指定したフォルダ内のSQLファイルを実行します。
	 * ファイル名の昇順で実行します。
	 *
	 * @return string 実行後のバージョン
	 */
	private function execute( string $dir ): string {
		// global $wpdb;

		// SQLファイル一覧を取得し、昇順にソート
		$files = scandir( $dir, SCANDIR_SORT_ASCENDING );

		// SQLファイルの内容を読みこむ
		foreach ( $files as $file ) {
			if ( false === is_file( $dir . '/' . $file ) || false === $this->is_sql_file( $file ) ) {
				continue;
			}

			$sql = file_get_contents( $dir . '/' . $file );

			// テーブル名等の置換を行う
			$sql = $this->replace_table_name( $sql );

			// SQLを実行(dbDeltaを使うために`upgrade.php`を読み込む)
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
			// $wpdb->query( $sql );
		}

		// SQLファイル実行後のバージョンを返す
		$exploded = explode( '-', basename( $dir ) );
		return $exploded[ count( $exploded ) - 1 ];
	}

	private function replace_table_name( string $sql ): string {
		$replaces = $this->getTableNameMappings();
		foreach ( $replaces as $key => $value ) {
			$sql = str_replace( $key, $value, $sql );
		}
		return $sql;
	}

	/**
	 * @return array<string,string> テーブル名の置換を行うための配列
	 */
	private function getTableNameMappings(): array {
		return array(
			'%HIST_SET_POST_TABLE%'        => TableName::getHistorySettingPostTableName(),
			'%HIST_TICKETS_TABLE%'         => TableName::getHistoryTicketsTableName(),
			'%HIST_PURCHASE_EVENTS_TABLE%' => TableName::getHistoryPurchaseEventsTableName(),
			'%HIST_LOGS_TABLE%'            => TableName::getLogsTableName(),
			// テーブルが追加された時はここに追加する。
		);
	}

	/**
	 * 指定したファイルがSQLファイルかどうかを判定します。
	 */
	private function is_sql_file( string $file_name ): bool {
		return substr( strtolower( $file_name ), -4 ) === '.sql';
	}


	public static function uninstall(): void {
		$instance = self::getInstance();

		// データベースバージョンをoptionsテーブルから削除
		$instance->removeVersionFromOptionTable();

		global $wpdb;

		// テーブル名置換の配列から、テーブル一覧を取得して削除
		$table_names = array_values( $instance->getTableNameMappings() );
		foreach ( $table_names as $table_name ) {
			$sql = <<<SQL
				DROP TABLE IF EXISTS {$table_name};
			SQL;
			$wpdb->query( $sql );
		}
	}
}
