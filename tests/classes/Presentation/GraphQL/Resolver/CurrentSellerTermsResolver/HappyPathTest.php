<?php
declare(strict_types=1);

namespace Cornix\Serendipity\TestCase\Presentation\GraphQL\Resolver\CurrentSellerTermsResolver;

use Cornix\Serendipity\Core\Infrastructure\Factory\TermsServiceFactory;
use Cornix\Serendipity\Test\Entity\WpUser;
use Cornix\Serendipity\Test\PHPUnit\UnitTestCaseBase;

class HappyPathTest extends UnitTestCaseBase {

	public static function setUpBeforeClass(): void {
		self::resetDatabase(); // データベースをリセット
	}

	private const CURRENT_SELLER_TERMS_QUERY = <<<GRAPHQL
		query CurrentSellerTerms {
			currentSellerTerms {
				version
				message
			}
		}
	GRAPHQL;

	/**
	 * 管理者は最新の販売者向け利用規約の情報を取得できることを確認
	 *
	 * @test
	 * @testdox [832BAB37][GraphQL] Request current seller terms success - user: $user
	 * @dataProvider requestValidUsersProvider
	 */
	public function requestCurrentSellerTermsSuccess( WpUser $user ) {
		// ARRANGE
		$current_seller_terms = ( new TermsServiceFactory() )->create()->getCurrentSellerTerms();

		// ACT
		$data = $this->graphQl( $user )->request(
			self::CURRENT_SELLER_TERMS_QUERY,
			array()
		)->get_data();

		// ASSERT
		$this->assertFalse( isset( $data['errors'] ) ); // エラーフィールドは存在しない

		// Repositoryから取得した値と一致していることを確認
		$this->assertEquals( $current_seller_terms->version()->value(), $data['data']['currentSellerTerms']['version'] );
		$this->assertEquals( $current_seller_terms->message()->value(), $data['data']['currentSellerTerms']['message'] );
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


	public function requestValidUsersProvider(): array {
		// 管理者のみ`currentSellerTerms`の呼び出しが可能
		return array(
			array( WpUser::admin() ),
		);
	}
	public function requestInvalidUsersProvider(): array {
		// 管理者以外は`currentSellerTerms`の呼び出しに失敗
		return array(
			array( WpUser::contributor() ),
			array( WpUser::visitor() ),
		);
	}
}
