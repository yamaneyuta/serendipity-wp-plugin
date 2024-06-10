<?php

use Cornix\Serendipity\Core\Lib\Env\Env;
use Cornix\Serendipity\Core\Lib\Path\LocalPath;
use Yoast\WPTestUtils\BrainMonkey\TestCase;

class EnvTest extends TestCase {

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
		$is_develpment = Env::isDevelopmentMode();

		// 開発環境はtureが返ること
		$this->assertTrue( $is_develpment );

		// TODO: package.jsonが無い場合のテスト
	}
}
