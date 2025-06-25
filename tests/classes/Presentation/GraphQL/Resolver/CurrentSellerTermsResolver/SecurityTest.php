<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Test\Presentation\GraphQL\Resolver\CurrentSellerTermsResolver;

use Cornix\Serendipity\Core\Infrastructure\Logging\ValueObject\LogLevel;
use Cornix\Serendipity\TestLib\Entity\WpUser;

class SecurityTest extends CurrentSellerTermsResolverBase {

	public static function setUpBeforeClass(): void {
		self::resetDatabase(); // データベースをリセット
	}

	public function setUp(): void {
		parent::setUp();
		$this->setAppLogLevel( LogLevel::none() );  // ログを抑制
	}

	/**
	 * 管理者以外は最新の販売者向け利用規約の情報を取得できないことを確認
	 *
	 * @test
	 * @testdox [969C0FAA][GraphQL] Request current seller terms fail - user: $user
	 * @dataProvider requestInvalidUsersProvider
	 */
	public function requestCurrentSellerTermsFail( WpUser $user ) {
		// ARRANGE
		// Do nothing

		// ACT
		$data = $this->graphQl( $user )->request(
			self::CURRENT_SELLER_TERMS_QUERY,
			array()
		)->get_data();

		// ASSERT
		$this->assertTrue( isset( $data['errors'] ) ); // エラーフィールドが存在する
	}
	public function requestInvalidUsersProvider(): array {
		// 管理者以外は`currentSellerTerms`の呼び出しに失敗
		return array(
			array( WpUser::contributor() ),
			array( WpUser::visitor() ),
		);
	}
}
