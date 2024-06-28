<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Features\Repository\Database\DBSchema;
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
		error_log( '[debug] before: ' . json_encode( $this->pluginTablesRemained( $wpdb ) ) );
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
		error_log( '[debug] after: ' . json_encode( $this->pluginTablesRemained( $wpdb ) ) );
		$this->assertNotCount( 0, $this->pluginTablesRemained( $wpdb ) );
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


	private function hoge() {

		// ★TODO テストコード側で確認のために使用する
		// $prefix = (new PluginInfo())->tableNamePrefix();

		// // ※ `%%`を使用してprepareを使用すると、`%`が`{e28fd42a5fa0b200f044426652734b453a63fc47f4006531b79da2a25051ee2e}`のような文字に
		// // 置き換えられ正常に検索できないため、prepareを使用せずにクエリを作成する。(`$prefix`はユーザーによる入力に影響しないため許容)
		// $results = $this->wpdb->get_results( "SHOW TABLES LIKE '{$prefix}%';", ARRAY_N );
		// if(isEmpty($results)) {
		// return;
		// }
		// $drop_tables = implode( ',', array_map( fn( $result ) => "`$result[0]`", $results ));    // 削除対象のテーブル名をカンマで連結
	}

	/**
	 * 現在のデータベースに存在している、本プラグインで使用するテーブル一覧を取得します。
	 *
	 * @return array<string>
	 */
	private function pluginTablesRemained( wpdb $wpdb ) {
		// プレフィックスで開始するテーブル一覧を取得
		$prefix  = ( new PluginInfo() )->tableNamePrefix();
		$results = $wpdb->get_results( "SHOW TABLES LIKE '{$prefix}%';", ARRAY_N );
		$tables  = array_map( fn( $result ) => $result[0], $results ); // SQLの戻り値から、テーブル名の配列(array<string>)に変換

		return $tables;
	}
}
