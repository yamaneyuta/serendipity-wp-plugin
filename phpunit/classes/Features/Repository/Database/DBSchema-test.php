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
	 * @dataProvider supportedWpdbProvider
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
	 * @dataProvider supportedWpdbProvider
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
	 * @dataProvider supportedWpdbProvider
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
	public function supportedWpdbProvider(): array {
		return array(
			array( $this->getWpdb() ),
			array( $this->getWpdb( 'mysql-phpunit-oldest' ) ),
			array( $this->getWpdb( 'mysql-phpunit-latest' ) ),
			array( $this->getWpdb( 'mysql-phpunit-not-support' ) ),
			array( $this->getWpdb( 'mariadb-phpunit-oldest' ) ),
			array( $this->getWpdb( 'mariadb-phpunit-latest' ) ),
			array( $this->getWpdb( 'mariadb-phpunit-not-support' ) ),
		);
	}

	private function getWpdb( string $host = null ): wpdb {
		if ( $host === null ) {
			return $GLOBALS['wpdb'];
		}

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
