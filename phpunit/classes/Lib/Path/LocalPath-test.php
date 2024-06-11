<?php

use Cornix\Serendipity\Core\Lib\Path\LocalPath;

class LocalPathTest extends WP_UnitTestCase {

	protected function set_up() {
		parent::set_up();
		// Your own additional setup.
	}

	protected function tear_down() {
		// Your own additional tear down.
		parent::tear_down();
	}

	/**
	 * @test
	 * @testdox [4222BB71] 本プラグインがインストールされているディレクトリパス
	 */
	public function get() {
		$package_json_path = LocalPath::get( 'package.json' );
		$work_dir          = explode( '/', plugin_basename( __FILE__ ) )[0];

		// テスト環境では`/var/www/html/wp-content/plugins`ディレクトリ以下に配置される
		$this->assertEquals( "/var/www/html/wp-content/plugins/{$work_dir}/package.json", $package_json_path );
		$this->assertTrue( file_exists( $package_json_path ) );

		// 相対パスで指定した場合は、`./`がパスの途中に現れる
		$package_json_path = LocalPath::get( './package.json' );
		$this->assertEquals( "/var/www/html/wp-content/plugins/{$work_dir}/./package.json", $package_json_path );
		$this->assertTrue( file_exists( $package_json_path ) ); // 問題なくパスの解決ができる

		// `/`から開始した場合
		$package_json_path = LocalPath::get( '/package.json' );
		$this->assertEquals( "/var/www/html/wp-content/plugins/{$work_dir}//package.json", $package_json_path );
		$this->assertTrue( file_exists( $package_json_path ) ); // 問題なくパスの解決ができる
	}
}
