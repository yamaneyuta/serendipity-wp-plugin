<?php

use Cornix\Serendipity\Core\Lib\Env\Env;

class EnvTest extends WP_UnitTestCase {

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
