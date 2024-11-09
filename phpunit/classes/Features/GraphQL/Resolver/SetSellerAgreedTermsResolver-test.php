<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Repository\SellerAgreedTerms;
use Cornix\Serendipity\Core\Lib\Repository\SellerTerms;
use Cornix\Serendipity\Core\Lib\Web3\Ethers;

class SetSellerAgreedTermsResolverTest extends IntegrationTestBase {

	private function requestSetSellerAgreedTerms( string $user_type, int $version, string $signature ) {
		// リクエストを送信するユーザーを設定
		$this->getUser( $user_type )->setCurrentUser();

		$query     = <<<GRAPHQL
			mutation SetSellerAgreedTerms(\$version: Int!, \$signature: String!) {
				setSellerAgreedTerms(version: \$version, signature: \$signature)
			}
		GRAPHQL;
		$variables = array(
			'version'   => $version,
			'signature' => $signature,
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
		// Aliceが署名(本来はフロントエンド側の処理)
		$alice                = HardhatSignerFactory::alice();
		$seller_terms_version = ( new SellerTerms() )->currentVersion();
		$seller_terms_message = ( new SellerTerms() )->message( $seller_terms_version );
		$signature            = $alice->signMessage( $seller_terms_message );
		// 事前チェック
		$this->assertFalse( ( new SellerAgreedTerms() )->exists() );  // データは保存されていないこと

		// ACT
		$data = $this->requestSetSellerAgreedTerms( $user_type, $seller_terms_version, $signature );

		// ASSERT
		// responseの確認
		$this->assertFalse( isset( $data['errors'] ) ); // エラーフィールドは存在しない
		// データの確認
		$this->assertTrue( ( new SellerAgreedTerms() )->exists() );   // 設定が保存されていること
		$this->assertEquals( $seller_terms_version, ( new SellerAgreedTerms() )->version() );
		$this->assertEquals( $seller_terms_message, ( new SellerAgreedTerms() )->message() );
		$this->assertEquals( $signature, ( new SellerAgreedTerms() )->signature() );
		// 保存済みのメッセージと署名からアドレスを取得できること
		$this->assertEquals( $alice->address(), Ethers::verifyMessage( ( new SellerAgreedTerms() )->message(), ( new SellerAgreedTerms() )->signature() ) );
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
		// Aliceが署名(本来はフロントエンド側の処理)
		$alice                = HardhatSignerFactory::alice();
		$seller_terms_version = ( new SellerTerms() )->currentVersion();
		$seller_terms_message = ( new SellerTerms() )->message( $seller_terms_version );
		$signature            = $alice->signMessage( $seller_terms_message );
		// 事前チェック
		$this->assertFalse( ( new SellerAgreedTerms() )->exists() );  // データは保存されていないこと

		// ACT
		$data = $this->requestSetSellerAgreedTerms( $user_type, $seller_terms_version, $signature );

		// ASSERT
		// responseの確認
		$this->assertTrue( isset( $data['errors'] ) ); // エラーフィールドが存在
		// データの確認
		$this->assertFalse( ( new SellerAgreedTerms() )->exists() );   // 設定が保存されていること
		$this->assertNull( ( new SellerAgreedTerms() )->version() );
		$this->assertNull( ( new SellerAgreedTerms() )->message() );
		$this->assertNull( ( new SellerAgreedTerms() )->signature() );
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
