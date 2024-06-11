<?php

use Cornix\Serendipity\Core\Lib\Path\Url;

class UrlTest extends WP_UnitTestCase {

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
	 * @testdox [6CDBF060] 本プラグインがインストールされているディレクトリ内に存在するファイルのURL
	 */
	public function get() {
		$package_json_path = Url::get( 'package.json' );
		$work_dir          = explode( '/', plugin_basename( __FILE__ ) )[0];

		// テスト環境では`http://localhost:8889/wp-content/plugins/...`がURLとして返される
		$this->assertEquals( "http://localhost:8889/wp-content/plugins/{$work_dir}/package.json", $package_json_path );

		// 相対パスで指定した場合は、`./`がパスの途中に現れる
		$package_json_path = Url::get( './package.json' );
		$this->assertEquals( "http://localhost:8889/wp-content/plugins/{$work_dir}/./package.json", $package_json_path );

		// ブロックスクリプトのURLを取得
		$block_script_path = Url::get( 'build/block/index.js' );
		$this->assertEquals( "http://localhost:8889/wp-content/plugins/{$work_dir}/build/block/index.js", $block_script_path );
	}

	/**
	 * @test
	 * @testdox [672DEA5E] サイトアドレス (URL)
	 */
	public function getSiteAddress() {
		$site_address = Url::getSiteAddress();
		$this->assertEquals( 'http://localhost:8889', $site_address );
	}
}
