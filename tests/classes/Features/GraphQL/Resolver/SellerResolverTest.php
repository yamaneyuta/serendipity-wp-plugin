<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Infrastructure\Web3\Ethers;
use Cornix\Serendipity\Core\Application\Factory\TermsServiceFactory;

class SellerResolverTest extends IntegrationTestBase {

	private function requestSeller( string $user_type ) {
		// リクエストを送信するユーザーを設定
		$this->getUser( $user_type )->setCurrentUser();

		$query     = <<<GRAPHQL
			query Seller {
				seller {
					agreedTerms {
						version
						message
						signature
					}
				}
			}
		GRAPHQL;
		$variables = array();

		// GraphQLリクエストを送信
		$data = $this->requestGraphQL( $query, $variables )->get_data();

		return $data;
	}

	/**
	 * 販売者の署名データが存在しない時にsellerフィールドを取得しても値がnullであることを確認
	 *
	 * @test
	 * @testdox [5C06E753][GraphQL] Request seller success(sign not exists) - user: $user_type
	 * @dataProvider requestValidUsersProvider
	 */
	public function requestSellerSuccessWithNoSignData( string $user_type ) {
		// ARRANGE
		// Do nothing

		// ACT
		$data = $this->requestSeller( $user_type );

		// ASSERT
		$this->assertTrue( isset( $data['data']['seller'] ) );  // data.sellerオブジェクトは存在する
		$this->assertNull( $data['data']['seller']['agreedTerms'] );    // 署名が保存されていないので値はnullが設定されている

		$this->assertFalse( isset( $data['errors'] ) ); // エラーフィールドは存在しない
	}


	/**
	 * 販売者の署名データが存在する場合は、その値を取得できることを確認
	 *
	 * @test
	 * @testdox [D9F3B7B6][GraphQL] Request seller success(sign exists) - user: $user_type
	 * @dataProvider requestValidUsersProvider
	 */
	public function requestSellerSuccessWithSignData( string $user_type ) {
		// ARRANGE
		// Aliceが販売者用利用規約に署名しデータを保存
		$alice         = HardhatSignerFactory::alice();
		$terms_service = ( new TermsServiceFactory() )->create();
		$seller_terms  = $terms_service->getCurrentSellerTerms();
		$signature     = $alice->signMessage( $seller_terms->message() );
		$terms_service->saveSellerSignature( $signature );

		// ACT
		$data = $this->requestSeller( $user_type );

		// ASSERT
		// 保存した値が取得できること
		$agreed_terms = $data['data']['seller']['agreedTerms'];
		$this->assertEquals( $seller_terms->version()->value(), $agreed_terms['version'] );
		$this->assertEquals( $seller_terms->message(), $agreed_terms['message'] );
		$this->assertEquals( $signature, $agreed_terms['signature'] );
		// 保存されたメッセージと署名からアドレスを取得できること
		$this->assertEquals( $alice->address(), Ethers::verifyMessage( $agreed_terms['message'], $agreed_terms['signature'] ) );

		$this->assertFalse( isset( $data['errors'] ) ); // エラーフィールドは存在しない
	}

	public function requestValidUsersProvider(): array {
		// 誰でも`seller`の呼び出しが可能
		return array(
			array( UserType::ADMINISTRATOR ),
			array( UserType::CONTRIBUTOR ),
			array( UserType::ANOTHER_CONTRIBUTOR ),
			array( UserType::VISITOR ),
		);
	}
}
