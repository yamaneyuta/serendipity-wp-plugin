<?php

use Cornix\Serendipity\Core\Lib\HandleName\HandleName;
use Yoast\WPTestUtils\BrainMonkey\TestCase;

class HandleNameTest extends TestCase {

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
	 * @testdox [DD13FF05] ブロックエディタのウィジェット用jsのハンドル名のチェック
	 */
	public function blockScript() {
		$handle = HandleName::blockScript();

		// ハンドル名は文字列かつ空文字でないこと
		$this->assertTrue( is_string( $handle ) );
		$this->assertTrue( strlen( $handle ) > 0 );
	}
}
