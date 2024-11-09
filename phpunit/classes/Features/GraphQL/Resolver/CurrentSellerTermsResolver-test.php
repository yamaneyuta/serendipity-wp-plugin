<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Repository\SellerTerms;

class CurrentSellerTermsResolverTest extends IntegrationTestBase {

	private function requestCurrentSellerTerms( string $user_type ) {
		// リクエストを送信するユーザーを設定
		$this->getUser( $user_type )->setCurrentUser();

		$query     = <<<GRAPHQL
			query CurrentSellerTerms {
				currentSellerTerms {
					version
					message
				}
			}
		GRAPHQL;
		$variables = array();

		// GraphQLリクエストを送信
		$data = $this->requestGraphQL( $query, $variables )->get_data();

		return $data;
	}

	/**
	 * 管理者は最新の販売者向け利用規約の情報を取得できることを確認
	 *
	 * @test
	 * @testdox [832BAB37][GraphQL] Request current seller terms success - user: $user_type
	 * @dataProvider requestValidUsersProvider
	 */
	public function requestCurrentSellerTermsSuccess( string $user_type ) {
		// ARRANGE
		// Do nothing

		// ACT
		$data = $this->requestCurrentSellerTerms( $user_type );

		// ASSERT
		$this->assertFalse( isset( $data['errors'] ) ); // エラーフィールドは存在しない

		// Repositoryから取得した値と一致していることを確認
		$version = ( new SellerTerms() )->currentVersion();
		$message = ( new SellerTerms() )->message( $version );
		$this->assertEquals( $version, $data['data']['currentSellerTerms']['version'] );
		$this->assertEquals( $message, $data['data']['currentSellerTerms']['message'] );
	}

	/**
	 * 管理者以外は最新の販売者向け利用規約の情報を取得できないことを確認
	 *
	 * @test
	 * @testdox [969C0FAA][GraphQL] Request current seller terms fail - user: $user_type
	 * @dataProvider requestInvalidUsersProvider
	 */
	public function requestCurrentSellerTermsFail( string $user_type ) {
		// ARRANGE
		// Do nothing

		// ACT
		$data = $this->requestCurrentSellerTerms( $user_type );

		// ASSERT
		$this->assertTrue( isset( $data['errors'] ) ); // エラーフィールドが存在する
	}


	public function requestValidUsersProvider(): array {
		// 管理者のみ`currentSellerTerms`の呼び出しが可能
		return array(
			array( UserType::ADMINISTRATOR ),
			// array( UserType::CONTRIBUTOR ),
			// array( UserType::ANOTHER_CONTRIBUTOR ),
			// array( UserType::VISITOR ),
		);
	}
	public function requestInvalidUsersProvider(): array {
		// 管理者以外は`currentSellerTerms`の呼び出しに失敗
		return array(
			// array( UserType::ADMINISTRATOR ),
			array( UserType::CONTRIBUTOR ),
			array( UserType::ANOTHER_CONTRIBUTOR ),
			array( UserType::VISITOR ),
		);
	}
}
