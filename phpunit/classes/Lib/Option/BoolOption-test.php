<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Repository\Name\Prefix;
use Cornix\Serendipity\Core\Lib\Option\BoolOption;

class BoolOptionTest extends IntegrationTestBase {

	/** テスト時にoptionsテーブルに書き込むためのkey(option_name) */
	private function getTestOptionKeyName(): string {
		$prefix = ( new Prefix() )->optionKeyPrefix();
		return $prefix . 'test_option';
	}

	/**
	 * 何も値を設定していない場合はnullが返ることを確認
	 *
	 * @test
	 * @testdox [92D2C669] BoolOption::get() - no data
	 */
	public function get_noData() {
		// ARRANGE
		$sut = new BoolOption( $this->getTestOptionKeyName() );

		// ACT
		$ret = $sut->get();

		// ASSERT
		$this->assertNull( $ret );
	}

	/**
	 * trueを設定した場合、trueを取得できるようになることを確認
	 *
	 * @test
	 * @testdox [2077A86B] BoolOption::update( true ) => BoolOption::get()
	 */
	public function set_true() {
		// ARRANGE
		$sut = new BoolOption( $this->getTestOptionKeyName() );

		// ACT
		$sut->update( true );
		$ret = $sut->get();

		// ASSERT
		$this->assertTrue( $ret );
	}

	/**
	 * falseを設定した場合、falseを取得できるようになることを確認
	 *
	 * @test
	 * @testdox [DEC294FB] BoolOption::update( false ) => BoolOption::get()
	 */
	public function set_false() {
		// ARRANGE
		$sut = new BoolOption( $this->getTestOptionKeyName() );

		// ACT
		$sut->update( false );
		$ret = $sut->get();

		// ASSERT
		$this->assertFalse( $ret );
	}

	/**
	 * 値の書き換えができることを確認
	 *
	 * @test
	 * @testdox [905B1F86] BoolOption::update - rewrite
	 */
	public function rewrite() {
		// ARRANGE
		$sut = new BoolOption( $this->getTestOptionKeyName() );
		$this->assertNull( $sut->get() );   // 初期状態はnull

		// ACT & ASSERT
		// 複数回書き換えを行い、値を確認する
		$sut->update( false );              // 1回目: falseを設定
		$this->assertFalse( $sut->get() );  // false

		$sut->update( true );               // 2回目: trueを設定
		$this->assertTrue( $sut->get() );   // true

		$sut->update( true );               // 3回目: 前回と同じtrueを設定
		$this->assertTrue( $sut->get() );   // true

		$sut->update( false );              // 4回目: falseを設定
		$this->assertFalse( $sut->get() );  // false

		$sut->update( false );              // 5回目: 前回と同じfalseを設定
		$this->assertFalse( $sut->get() );  // false
	}
}
