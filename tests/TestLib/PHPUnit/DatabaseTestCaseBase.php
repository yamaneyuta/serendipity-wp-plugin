<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Test\PHPUnit;

use Cornix\Serendipity\Core\Infrastructure\Database\MySQLiFactory;
use Cornix\Serendipity\Core\Presentation\PluginUpdateHook;
use Cornix\Serendipity\Core\Repository\Name\Prefix;
use wpdb;

/** データベースを使用するテストケース */
class DatabaseTestCaseBase extends UnitTestCaseBase {

	/** @inheritdoc */
	public function setUp(): void {
		parent::setUp();
		// ここに必要なセットアップ処理を追加

		// データベースのクリーンアップを実行
		( new CleanUpDatabase() )->handle( $GLOBALS['wpdb'] );
	}

	/** @inheritdoc */
	public function tearDown(): void {
		parent::tearDown();
		// ここに必要なクリーンアップ処理を追加
	}
}

class CleanUpDatabase {
	public function handle( wpdb $wpdb ): void {
		// テーブル、オプション、トランジェントをすべて削除
		$this->deleteAllTables( $wpdb );
		$this->deleteAllOptions( $wpdb );
		$this->deleteAllTransients( $wpdb );

		// データベースの初期化処理を実行
		$this->initializeDatabase();
	}

	/** このプラグインが作成したテーブルをすべて削除します */
	private function deleteAllTables( wpdb $wpdb ): void {
		$table_prefix = ( new Prefix() )->tableNamePrefix();

		$table_names = $wpdb->get_col( "SHOW TABLES LIKE '{$table_prefix}%'" );
		$mysqli      = ( new MySQLiFactory() )->create( $wpdb );
		foreach ( $table_names as $table_name ) {
			$result = $mysqli->query( "DROP TABLE IF EXISTS `{$table_name}`" );
			if ( false === $result ) {
				throw new \RuntimeException( "[1DE69773] Failed to drop table: {$table_name}" );
			}
		}
	}

	/** このプラグインが使用するオプションをすべて削除します */
	private function deleteAllOptions( wpdb $wpdb ): void {
		$option_prefix = ( new Prefix() )->optionKeyPrefix();
		$options       = $wpdb->get_col( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '{$option_prefix}%'" );

		foreach ( $options as $option_name ) {
			$result = delete_option( $option_name );
			if ( false === $result ) {
				throw new \RuntimeException( "[CD6EF671] Failed to delete option: {$option_name}" );
			}
		}
	}

	/** このプラグインが使用するトランジェントをすべて削除します */
	private function deleteAllTransients( wpdb $wpdb ): void {
		$transient_prefix = ( new Prefix() )->transientKeyPrefix();
		$transients       = $wpdb->get_col( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_{$transient_prefix}%'" );
		foreach ( $transients as $transient_name ) {
			$result = delete_transient( str_replace( '_transient_', '', $transient_name ) );
			if ( false === $result ) {
				throw new \RuntimeException( "[FBAD3EF3] Failed to delete transient: {$transient_name}" );
			}
		}
	}

	private function initializeDatabase(): void {
		// admin_initの代わりにPluginUpdateHookを呼び出す
		// ⇒Optionsテーブルが初期化されているので、プラグインの初期インストール処理が実行される
		$current_screen = get_current_screen();
		set_current_screen( 'index.php' );
		( new PluginUpdateHook() )->addActionAdminInit();
		set_current_screen( $current_screen ?? '' );
	}
}
