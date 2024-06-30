<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Features\Repository\Database\DBSchema;
use Cornix\Serendipity\Core\Features\Repository\Database\MySQLiFactory;
use Cornix\Serendipity\Core\Lib\Repository\Option\Option;
use Cornix\Serendipity\Core\Lib\SystemInfo\PluginInfo;

/**
 * プラグイン用テーブルのマイグレーションテスト
 */
class DBSchemaTest extends WP_UnitTestCase {

	// #[\Override]
	public function setUp(): void {
		parent::setUp();
		// Your own additional setup.

		// 他のDBに接続してマイグレーションのテストを実施するような場合でも、
		// optionsテーブルは`wp-env`が起動した`tests-mysql`を参照している点に注意。
		// - テーブル操作: 対象のDB
		// - スキーマバージョン操作: `tests-mysql`固定
		( new Option() )->uninstall();
		$current_version = ( new Option() )->getDBSchemaVersion();
		$this->assertEquals( '0.0.0', $current_version );   // 毎回初期化されていることを確認
	}

	// #[\Override]
	public function tearDown(): void {
		// Your own additional tear down.
		parent::tearDown();
	}


	/**
	 * @test
	 * @testdox [AC325463] DBSchema::migrage()
	 * @dataProvider wpdbListProvider
	 */
	public function migrate( wpdb $wpdb ) {
		$sut = new DBSchema( $wpdb );
		$sut->uninstall();
		$this->assertCount( 0, $this->pluginTablesRemained( $wpdb ) );

		$err = null;
		try {
			$sut->migrate();
		} catch ( Exception $e ) {
			$err = $e;
		}

		// マイグレーションで例外が発生していないこと
		$this->assertNull( $err );
		// 本プラグイン用のテーブルが1つ以上作成されていること
		$this->assertNotCount( 0, $this->pluginTablesRemained( $wpdb ) );
	}

	/**
	 * @test
	 * @testdox [FE9AEE90] DBSchema::rollback()
	 * @dataProvider wpdbListProvider
	 */
	public function rollback( wpdb $wpdb ) {
		$sut = new DBSchema( $wpdb );
		$sut->uninstall();
		$sut->migrate();
		$this->assertNotCount( 0, $this->pluginTablesRemained( $wpdb ) );

		$err = null;
		try {
			$sut->rollback();
		} catch ( Exception $e ) {
			$err = $e;
		}

		// ロールバックで例外が発生していないこと
		$this->assertNull( $err );
		// 本プラグイン用のテーブルが0個になっていること
		$this->assertCount( 0, $this->pluginTablesRemained( $wpdb ) );
	}

	/**
	 * @test
	 * @testdox [EE372BF5] DBSchema::uninstall()
	 * @dataProvider wpdbListProvider
	 */
	public function uninstall( wpdb $wpdb ) {
		$sut = new DBSchema( $wpdb );
		$sut->uninstall();
		$sut->migrate();
		$this->assertNotCount( 0, $this->pluginTablesRemained( $wpdb ) );

		$err = null;
		try {
			$sut->uninstall();
		} catch ( Exception $e ) {
			$err = $e;
		}

		// アンインストールで例外が発生していないこと
		$this->assertNull( $err );
		// 本プラグイン用のテーブルが0個になっていること
		$this->assertCount( 0, $this->pluginTablesRemained( $wpdb ) );
	}


	/**
	 * @return array<array<wpdb>>
	 */
	public function wpdbListProvider(): array {
		$wp_list = $this->wpdbList();
		return array_map( fn( wpdb $wpdb ) => array( $wpdb ), $wp_list );
	}

	/**
	 *
	 * @return wpdb[]
	 */
	protected function wpdbList(): array {
		/** @var wpdb[] $ret */
		$ret = array();

		// `wp-env`が起動した`tests-mysql`に接続するwpdb
		$ret[] = $GLOBALS['wpdb'];

		// 別途立ち上げたデータベースに接続するwpdb
		foreach ( $this->extDatabaseHosts() as $host ) {
			$ret[] = $this->createWpdb( $host );
		}

		return $ret;
	}

	private function extDatabaseHosts(): array {
		// SQL発行テスト用に立ち上げたデータベースのホスト名一覧
		// ※ `wp-env`が立ち上げた`tests-mysql`は含まない
		return array(
			'mysql-phpunit-oldest',
			'mysql-phpunit-latest',
			'mariadb-phpunit-oldest',
			'mariadb-phpunit-latest',
		);
	}

	private function createWpdb( string $host ): wpdb {
		assert( $host !== $GLOBALS['wpdb']->dbhost );

		// phpcsでフォーマットを行うと'WordPress'が'WordPress'に変換されるためphpcs:ignoreを指定
		$wpdb = new wpdb( 'root', 'password', 'wordpress', $host ); // phpcs:ignore
		assert( strpos( $host, 'mysql' ) !== false || strpos( $host, 'mariadb' ) !== false );
		$wpdb->is_mysql = true;
		$wpdb->charset  = 'utf8mb4';

		return $wpdb;
	}

	/**
	 * 現在のデータベースに存在している、本プラグインで使用するテーブル一覧を取得します。
	 *
	 * @return array<string>
	 */
	private function pluginTablesRemained( wpdb $wpdb ) {
		// プレフィックスで開始するテーブル一覧を取得
		$prefix = ( new PluginInfo() )->tableNamePrefix();

		$mysqli  = ( new MySQLiFactory() )->create( $wpdb );
		$results = $mysqli->query( "SHOW TABLES LIKE '{$prefix}%';" )->fetch_all();

		$ret = array();
		foreach ( $results as $row ) {
			assert( is_array( $row ) );
			$ret[] = $row[0];
		}

		return $ret;
	}
}
