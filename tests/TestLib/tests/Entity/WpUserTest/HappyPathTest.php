<?php
declare(strict_types=1);

use Cornix\Serendipity\Test\Entity\WpUser;
use Cornix\Serendipity\Test\PHPUnit\UnitTestCaseBase;

class HappyPathTest extends UnitTestCaseBase {

	/**
	 * 初期状態はログインしていないため訪問者がユーザーとして取得される
	 *
	 * @test
	 * @testdox [A07783E3] default user is visitor
	 */
	public function defaultUser(): void {
		// ARRANGE
		// Do nothing

		// ACT
		$current_user = WpUser::current();

		// ASSERT
		$this->assertEquals( WpUser::visitor(), $current_user );
	}


	/**
	 * ユーザーの切り替えができることを確認する
	 *
	 * @test
	 * @testdox [B1A2C3D4] switch user - $user
	 * @dataProvider switchUserProvider
	 */
	public function switchUser( WpUser $user ): void {
		// ARRANGE
		$this->assertEquals( WpUser::visitor(), WpUser::current() );

		// ACT
		$user->setCurrent();

		// ASSERT
		$this->assertEquals( WpUser::current(), $user );
	}
	public function switchUserProvider(): array {
		return array(
			array( WpUser::admin() ),
			array( WpUser::contributor() ),
			array( WpUser::anotherContributor() ),
			// visitorは初期状態とおなじため別のテストケースで確認する
		);
	}


	/**
	 * 複数回ユーザーを切り替えることができることを確認する
	 *
	 * @test
	 * @testdox [8E374A44] multi switch user - $user1 -> $user2
	 * @dataProvider multiSwitchUserProvider
	 * @param WpUser $user1
	 * @param WpUser $user2
	 */
	public function multiSwitchUser( WpUser $user1, WpUser $user2 ): void {
		// ARRANGE
		$this->assertEquals( WpUser::visitor(), WpUser::current() );

		// ACT
		$user1->setCurrent();
		$user1_switched = WpUser::current();

		$user2->setCurrent();
		$user2_switched = WpUser::current();

		// ASSERT
		$this->assertEquals( $user1, $user1_switched );
		$this->assertEquals( $user2, $user2_switched );
	}
	public function multiSwitchUserProvider(): array {
		return array(
			array( WpUser::admin(), WpUser::contributor() ),
			array( WpUser::contributor(), WpUser::anotherContributor() ),
			array( WpUser::anotherContributor(), WpUser::visitor() ),
			array( WpUser::visitor(), WpUser::admin() ),
		);
	}


	/**
	 * ユーザーが`wp_users`テーブルに存在するかどうかを確認する
	 *
	 * @test
	 * @testdox [E7930259] user: $user, expected: $expected
	 * @dataProvider existsUserProvider
	 */
	public function existsUser( WpUser $user, bool $expected ): void {
		// ARRANGE
		// Do nothing

		// ACT
		$user->setCurrent();
		$result = get_user_by( 'login', $user->name() ); // ユーザーを取得して存在確認

		// ASSERT
		$this->assertEquals( $expected, ( $result instanceof \WP_User && $result->exists() ) );
	}
	public function existsUserProvider(): array {
		return array(
			// user, is_exists
			array( WpUser::admin(), true ),
			array( WpUser::contributor(), true ),
			array( WpUser::anotherContributor(), true ),
			array( WpUser::visitor(), false ),
		);
	}
}
