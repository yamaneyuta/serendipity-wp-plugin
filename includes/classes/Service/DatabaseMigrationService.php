<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Service;

use Cornix\Serendipity\Core\Constant\Config;
use Cornix\Serendipity\Core\Infrastructure\Database\TableMigration\DatabaseMigrationBase;
use Cornix\Serendipity\Core\Repository\Environment;
use wpdb;

class DatabaseMigrationService {

	public function __construct( wpdb $wpdb, Environment $environment ) {
		$version_dir = Config::ROOT_DIR . '/includes/classes/Infrastructure/Database/TableMigration/versions';
		assert( is_dir( $version_dir ), '[E08E775C] Database migration directory does not exist: ' . $version_dir );

		$this->wpdb        = $wpdb;
		$this->environment = $environment;
		$this->version_dir = $version_dir;
	}
	private wpdb $wpdb;
	private Environment $environment;
	private string $version_dir;

	public function migrate( ?string $current_plugin_version, ?string $target_plugin_version = null ): void {

		// 現在のDBバージョンから適用するバージョン一覧を取得
		$migration_versions = ( new MigrationVersions( $this->version_dir ) )->migrationTargets( $current_plugin_version, $target_plugin_version );
		usort( $migration_versions, 'version_compare' );  // マイグレーションバージョンを昇順にソート

		/** @var DatabaseMigrationBase[] */
		$instances = array();
		try {
			foreach ( $migration_versions as $version ) {
				$version_files = $this->getMigrationFilePaths( $version );

				foreach ( $version_files as $file ) {
					$instance = require $file; // テスト時に複数回呼ばれるため`require_once`ではなく`require`を使用
					assert( is_object( $instance ) && $instance instanceof DatabaseMigrationBase, '[708AF988] invalid instance. file: ' . realpath( $file ) );    // 型を確認
					$instances[] = $instance; // インスタンスを配列に追加

					// インスタンスのプロパティを設定してマイグレーションを実行
					$instance->setWpdb( $this->wpdb );
					$instance->setEnvironment( $this->environment );
					$instance->up();
				}
			}
		} catch ( \Throwable $e ) {
			// 逆順にdown()を呼び出してロールバック
			$instances = array_reverse( $instances );
			foreach ( $instances as $instance ) {
				try {
					$instance->down(); // 失敗した場合はロールバック
				} catch ( \Throwable $rollbackException ) {
					// ここでは例外を再スローしない
					// TODO: loggerに置き換え
					error_log( '[0180E3B4] Rollback failed: ' . (string) $rollbackException );
				}
			}

			throw $e; // 元の例外を再スロー
		}
	}

	/**
	 * 指定したバージョンのディレクトリに存在するマイグレーションファイル一覧を取得します。
	 *
	 * @param string $version
	 * @return string[]
	 */
	private function getMigrationFilePaths( string $version ): array {
		$version_dir = $this->version_dir . DIRECTORY_SEPARATOR . $version;
		assert( is_dir( $version_dir ), '[924A3658] Migration version directory does not exist: ' . $version_dir );

		$migration_files = array();
		foreach ( scandir( $version_dir ) as $item ) {
			if ( $item === '.' || $item === '..' ) {
				continue;
			}
			if ( pathinfo( $item, PATHINFO_EXTENSION ) === 'php' ) {
				$migration_files[] = $version_dir . DIRECTORY_SEPARATOR . $item;
			}
		}

		assert( ! empty( $migration_files ), '[4B89FAB1] No migration files found for version: ' . $version );
		return $migration_files;
	}
}

/** @internal */
class MigrationVersions {
	public function __construct( string $version_dir ) {
		$this->version_dir = $version_dir;
	}

	private string $version_dir;

	/**
	 * 全てのマイグレーションバージョンを取得します。
	 *
	 * @return string[]
	 */
	private function all(): array {
		$versions = array();
		foreach ( scandir( $this->version_dir ) as $item ) {
			if ( $item === '.' || $item === '..' ) {
				continue;
			}
			// ディレクトリ名がバージョンとなっているので戻り値に追加
			if ( is_dir( $this->version_dir . DIRECTORY_SEPARATOR . $item ) ) {
				assert( false !== version_compare( $item, '0.0.0' ), '[15E96F3C] Invalid version format: ' . $item );
				assert( $item === strtolower( $item ), '[9D4BEF68] Version name must be lowercase: ' . $item );
				$versions[] = $item;
			}
		}
		return $versions;
	}

	/**
	 * 指定されたDBバージョンから適用するバージョン一覧を取得します。
	 * 引数がnullの場合は、全てのバージョンを返します。
	 *
	 * @param null|string $premigration_version 現在のDBバージョン。これより新しいバージョンに限定します。(この指定されたバージョンを含まない)
	 * @param null|string $target_version  ターゲットバージョン。このバージョン以下に限定します。(この指定されたバージョンを含む)
	 * @return string[]
	 */
	public function migrationTargets( ?string $premigration_version, ?string $target_version ): array {
		$migration_versions = $this->all();

		if ( $premigration_version !== null ) {
			assert( in_array( $premigration_version, $migration_versions, true ), '[FE2A508D] $current_version: ' . $premigration_version, ', $migration_versions: ' . json_encode( $migration_versions ) );
			$migration_versions = array_filter( $migration_versions, fn( $version ) => version_compare( $premigration_version, $version, '<' ) );
			assert( in_array( $premigration_version, $migration_versions, true ), '[910486A0] $current_version: ' . $premigration_version, ', $migration_versions: ' . json_encode( $migration_versions ) );
		}

		if ( $target_version !== null ) {
			assert( in_array( $target_version, $migration_versions, true ), '[5B9D22F7] $target_version: ' . $target_version, ', $migration_versions: ' . json_encode( $migration_versions ) );
			$migration_versions = array_filter( $migration_versions, fn( $version ) => version_compare( $version, $target_version, '<=' ) );
			assert( in_array( $target_version, $migration_versions, true ), '[7DD7AB11] $target_version: ' . $target_version, ', $migration_versions: ' . json_encode( $migration_versions ) );
		}

		return $migration_versions;
	}
}
