<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Domain\Service\SellerService;
use Cornix\Serendipity\Core\Domain\Service\WalletService;
use Cornix\Serendipity\Core\Domain\ValueObject\Signature;
use Cornix\Serendipity\Core\Infrastructure\Web3\Ethers;
use Cornix\Serendipity\Core\Infrastructure\Factory\TermsServiceFactory;

class SetSellerAgreedTermsResolverTest extends IntegrationTestBase {

	private function requestSetSellerAgreedTerms( string $user_type, int $version, Signature $signature ) {
		// リクエストを送信するユーザーを設定
		$this->getUser( $user_type )->setCurrentUser();

		$query     = <<<GRAPHQL
			mutation SetSellerAgreedTerms(\$version: Int!, \$signature: String!) {
				setSellerAgreedTerms(version: \$version, signature: \$signature)
			}
		GRAPHQL;
		$variables = array(
			'version'   => $version,
			'signature' => $signature->value(),
		);

		// GraphQLリクエストを送信
		$data = $this->requestGraphQL( $query, $variables )->get_data();

		return $data;
	}

	/**
	 * 管理者が署名データを保存できることを確認
	 *
	 * @test
	 * @testdox [D48AB2DA][GraphQL] Success request set seller agreed terms - user: $user_type
	 * @dataProvider requestValidUsersProvider
	 */
	public function requestSetSellerSuccess( string $user_type ) {
		// ARRANGE
		$seller_service       = $this->container()->get( SellerService::class );
		$wallet_service       = $this->container()->get( WalletService::class );
		$terms_service        = ( new TermsServiceFactory() )->create();
		$current_seller_terms = $terms_service->getCurrentSellerTerms();
		// Aliceが署名(本来はフロントエンド側の処理)
		$alice     = HardhatSignerFactory::alice();
		$signature = $wallet_service->signMessage( $alice, $current_seller_terms->message() );
		// 事前チェック
		$this->assertNull( $seller_service->getSellerSignedTerms() );  // 署名済みのデータは保存されていないこと

		// ACT
		$data = $this->requestSetSellerAgreedTerms( $user_type, $current_seller_terms->version()->value(), $signature );

		// ASSERT
		// responseの確認
		$this->assertFalse( isset( $data['errors'] ) ); // エラーフィールドは存在しない
		// データの確認
		$signed_seller_terms = $seller_service->getSellerSignedTerms();
		// 設定が保存されていること
		$this->assertTrue( $signed_seller_terms->terms()->version()->equals( $current_seller_terms->version() ) );
		$this->assertTrue( $signed_seller_terms->terms()->message()->value() === $current_seller_terms->message()->value() );
		// 保存済みのメッセージと署名からアドレスを取得できること
		$this->assertEquals( $alice->address(), Ethers::verifyMessage( $signed_seller_terms->terms()->message(), $signed_seller_terms->signature() ) );
	}


	/**
	 * 管理者以外は署名データを保存できないことを確認
	 *
	 * @test
	 * @testdox [9989CB11][GraphQL] Fail request set seller agreed terms - user: $user_type
	 * @dataProvider requestInvalidUsersProvider
	 */
	public function requestSetSellerFail( string $user_type ) {
		// ARRANGE
		$seller_service       = $this->container()->get( SellerService::class );
		$wallet_service       = $this->container()->get( WalletService::class );
		$terms_service        = ( new TermsServiceFactory() )->create();
		$current_seller_terms = $terms_service->getCurrentSellerTerms();
		// Aliceが署名(本来はフロントエンド側の処理)
		$alice     = HardhatSignerFactory::alice();
		$signature = $wallet_service->signMessage( $alice, $current_seller_terms->message() );
		// 事前チェック
		$this->assertNull( $seller_service->getSellerSignedTerms() );  // データは保存されていないこと

		// ACT
		$data = $this->requestSetSellerAgreedTerms( $user_type, $current_seller_terms->version()->value(), $signature );

		// ASSERT
		// responseの確認
		$this->assertTrue( isset( $data['errors'] ) ); // エラーフィールドが存在
		$this->assertNull( $seller_service->getSellerSignedTerms() );  // データは保存されていないこと
	}


	public function requestValidUsersProvider(): array {
		// `setSellerAgreedTerms`の呼び出しは管理者のみ有効
		return array(
			array( UserType::ADMINISTRATOR ),
			// array( UserType::CONTRIBUTOR ),
			// array( UserType::ANOTHER_CONTRIBUTOR ),
			// array( UserType::VISITOR ),
		);
	}
	public function requestInvalidUsersProvider(): array {
		// 管理者以外は`setSellerAgreedTerms`の呼び出しに失敗する
		return array(
			// array( UserType::ADMINISTRATOR ),
			array( UserType::CONTRIBUTOR ),
			array( UserType::ANOTHER_CONTRIBUTOR ),
			array( UserType::VISITOR ),
		);
	}
}
