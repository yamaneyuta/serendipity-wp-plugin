<?php

use Cornix\Serendipity\Core\Lib\Strings\Strings;

class StringsTest extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		// Your own additional setup.
	}

	public function tear_down() {
		$this->setMbstringEnabled( null ); // cleanup
		// Your own additional tear down.
		parent::tear_down();
	}

	/**
	 * mbstringが有効(読みこまれている)かどうかを強制的に設定します。
	 *
	 * @param bool|null $enabled
	 */
	private function setMbstringEnabled( $enabled ) {
		$prop = new ReflectionProperty( Strings::class, 'IS_MBSTRING_ENABLED' );
		$prop->setAccessible( true );
		$prop->setValue( null, $enabled );
	}

	/**
	 * @test
	 * @testdox [FC5284D8] String::substr()
	 */
	public function substr() {
		// 組み込み関数のラッパーなので詳細なテストは不要。
		$this->setMbstringEnabled( null );
		$this->assertEquals( 'bcd', Strings::substr( 'abcdef', 1, 3 ) );
		$this->assertEquals( 'bcd', Strings::substr( 'abcdef', 1, 3, 'utf-8' ) );

		$this->setMbstringEnabled( true );
		$this->assertEquals( 'bcd', Strings::substr( 'abcdef', 1, 3 ) );
		$this->assertEquals( 'bcd', Strings::substr( 'abcdef', 1, 3, 'utf-8' ) );
		$this->assertEquals( 'いうえ', Strings::substr( 'あいうえお', 1, 3 ) );
		$this->assertEquals( 'いうえ', Strings::substr( 'あいうえお', 1, 3, 'utf-8' ) );

		$this->setMbstringEnabled( false );
		$this->assertEquals( 'bcd', Strings::substr( 'abcdef', 1, 3 ) );
		$this->assertEquals( 'bcd', Strings::substr( 'abcdef', 1, 3, 'utf-8' ) );
		// mbstringが無効のためマルチバイト文字列はテストしない
	}

	/**
	 * @test
	 * @testdox [62FACDFA] String::strpos()
	 */
	public function strpos() {
		// 組み込み関数のラッパーなので詳細なテストは不要。

		$this->setMbstringEnabled( true );
		$this->assertEquals( 1, Strings::strpos( 'abcdef', 'bc' ) );
		$this->assertEquals( 1, Strings::strpos( 'あいうえお', 'いう' ) );
		$this->assertEquals( false, Strings::strpos( 'abcdef', 'xyz' ) ); // 見つからない場合はfalse

		$this->setMbstringEnabled( false );
		$this->assertEquals( 1, Strings::strpos( 'abcdef', 'bc' ) );
		$this->assertEquals( false, Strings::strpos( 'abcdef', 'xyz' ) );   // 見つからない場合はfalse
	}

	/**
	 * @test
	 * @testdox [C00E3407] String::all_strpos()
	 */
	public function all_strpos() {
		$this->setMbstringEnabled( true );
		$this->assertEquals( array( 1, 7 ), Strings::all_strpos( 'abcbdefbc', 'bc' ) );
		$this->assertEquals( array( 1, 6 ), Strings::all_strpos( 'あいうえおあいう', 'いう' ) );
		$this->assertEquals( array(), Strings::all_strpos( 'abcbdefbc', 'xyz' ) ); // 見つからない場合は空配列

		$this->setMbstringEnabled( false );
		$this->assertEquals( array( 1, 7 ), Strings::all_strpos( 'abcbdefbc', 'bc' ) );
		$this->assertEquals( array(), Strings::all_strpos( 'abcbdefbc', 'xyz' ) ); // 見つからない場合は空配列
	}

	/**
	 * @test
	 * @testdox [D868AF03] String::strlen()
	 */
	public function strlen() {
		// 組み込み関数のラッパーなので詳細なテストは不要。

		$this->setMbstringEnabled( true );
		$this->assertEquals( 6, Strings::strlen( 'abcdef' ) );
		$this->assertEquals( 5, Strings::strlen( 'あいうえお' ) );
		$this->assertEquals( 0, Strings::strlen( '' ) );

		$this->setMbstringEnabled( false );
		$this->assertEquals( 6, Strings::strlen( 'abcdef' ) );
		$this->assertEquals( 0, Strings::strlen( '' ) );
	}

	/**
	 * @test
	 * @testdox [8C9DB629] String::starts_with()
	 */
	public function starts_with() {
		$this->setMbstringEnabled( true );
		$this->assertTrue( Strings::starts_with( 'abcdef', 'ab' ) );
		$this->assertTrue( Strings::starts_with( 'あいうえお', 'あい' ) );
		$this->assertFalse( Strings::starts_with( 'abcdef', 'bc' ) );
		$this->assertFalse( Strings::starts_with( 'あいうえお', 'いう' ) );

		$this->setMbstringEnabled( false );
		$this->assertTrue( Strings::starts_with( 'abcdef', 'ab' ) );
		$this->assertFalse( Strings::starts_with( 'abcdef', 'bc' ) );
	}
}
