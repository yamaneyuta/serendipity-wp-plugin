<?php
declare(strict_types=1);

class AllNetworkCategoriesResolverTest extends IntegrationTestBase {

	private function requestNetworkCategories( string $user_type ) {
		// リクエストを送信するユーザーを設定
		$this->getUser( $user_type )->setCurrentUser();

		$query     = <<<GRAPHQL
			query AllNetworkCategories {
				allNetworkCategories {
					id
					chains {
						id
					}
					sellableSymbols
				}
			}
		GRAPHQL;
		$variables = array();

		// GraphQLリクエストを送信
		$data = $this->requestGraphQL( $query, $variables )->get_data();

		return $data;
	}

	/**
	 * @test
	 * @testdox [D98E9329][GraphQL] networkCategoriesSuccess - user: $user_type
	 * @dataProvider networkCategoriesSuccessProvider
	 */
	public function networkCategoriesSuccess( string $user_type ) {

		$data = $this->requestNetworkCategories( $user_type );

		$this->assertTrue( isset( $data['data'] ) );    // 設定が取得できる
		$this->assertFalse( isset( $data['errors'] ) ); // エラーフィールドが存在しない
	}

	public function networkCategoriesSuccessProvider(): array {
		// 投稿を編集できる権限がある場合、`allNetworkCategories`の呼び出しが可能
		return array(
			array( UserType::ADMINISTRATOR ),
			array( UserType::CONTRIBUTOR ),
			array( UserType::ANOTHER_CONTRIBUTOR ),
			// array( UserType::VISITOR ),
		);
	}

	// --------------------------------------------------------------------------------

	/**
	 * @test
	 * @testdox [8F8702CC][GraphQL] networkCategoriesFail - user: $user_type
	 * @dataProvider networkCategoriesFailProvider
	 */
	public function networkCategoriesFail( string $user_type ) {

		$data = $this->requestNetworkCategories( $user_type );

		$this->assertFalse( isset( $data['data'] ) );   // 設定が取得できない
		$this->assertTrue( isset( $data['errors'] ) );  // エラーフィールドが存在する
		// メッセージは内部エラー
		$this->assertEquals( $data['errors'][0]['message'], 'Internal server error' );
	}

	public function networkCategoriesFailProvider(): array {
		// 投稿を編集できる権限がない場合、`allNetworkCategories`の呼び出しが失敗する
		return array(
			// array( UserType::ADMINISTRATOR ),
			// array( UserType::CONTRIBUTOR ),
			// array( UserType::ANOTHER_CONTRIBUTOR ),
			array( UserType::VISITOR ),
		);
	}
}
