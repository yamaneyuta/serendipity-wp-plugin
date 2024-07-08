<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Features\Migration\DBSchema;
use Cornix\Serendipity\Core\Features\Migration\MySQLiFactory;
use Cornix\Serendipity\Core\Lib\Repository\DBSchemaVersion;
use Cornix\Serendipity\Core\Features\Uninstall\OptionUninstaller;
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
		( new OptionUninstaller() )->execute();
		$current_version = ( new DBSchemaVersion() )->get();
		$this->assertEquals( '0.0.0', $current_version );   // 毎回初期化されていることを確認
	}

	// #[\Override]
	public function tearDown(): void {
		// Your own additional tear down.
		parent::tearDown();
	}


	/**
	 * @test
	 * @testdox [AC325463] DBSchema::migrage() - host: $host
	 * @dataProvider wpdbListProvider
	 */
	public function migrate( string $host ) {
		$wpdb = WpdbFactory::create( $host );
		$sut  = new DBSchema( $wpdb );
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
	 * @testdox [FE9AEE90] DBSchema::rollback() - host: $host
	 * @dataProvider wpdbListProvider
	 */
	public function rollback( string $host ) {
		$wpdb = WpdbFactory::create( $host );
		$sut  = new DBSchema( $wpdb );
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
	 * @testdox [EE372BF5] DBSchema::uninstall() - host: $host
	 * @dataProvider wpdbListProvider
	 */
	public function uninstall( string $host ) {
		$wpdb = WpdbFactory::create( $host );
		$sut  = new DBSchema( $wpdb );
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
	 * @return array<array<string>>
	 */
	public function wpdbListProvider(): array {
		return ( new TestPattern() )->createDBHostMatrix();
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
