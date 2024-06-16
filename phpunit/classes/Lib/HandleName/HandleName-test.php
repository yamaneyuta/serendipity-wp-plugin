<?php

use Cornix\Serendipity\Core\Lib\HandleName\HandleName;

class HandleNameTest extends WP_UnitTestCase {

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
