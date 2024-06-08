<?php

use Cornix\Serendipity\Core\Lib\Path\LocalPath;
use Yoast\WPTestUtils\BrainMonkey\TestCase;

class LocalPathTest extends TestCase {

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
	public function example() {
		$package_json_path = LocalPath::get( 'package.json' );

		$this->assertEquals( $package_json_path, '/var/www/html/wp-content/plugins/workspaces/package.json' );
	}
}
