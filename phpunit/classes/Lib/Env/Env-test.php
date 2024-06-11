<?php

use Cornix\Serendipity\Core\Lib\Env\Env;

class EnvTest extends WP_UnitTestCase {

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
