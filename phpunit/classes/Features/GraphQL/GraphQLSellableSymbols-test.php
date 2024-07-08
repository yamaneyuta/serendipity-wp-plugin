<?php
declare(strict_types=1);

/**
 * sellableSymbolsを取得するGraphQLのテスト
 */
class GraphQLSellableSymbolsTest extends IntegrationTestBase {

	// #[\Override]
	public function setUp(): void {
		parent::setUp();
		// Your own additional setup.

		$this->initializeDatabase();
	}

	// #[\Override]
	public function tearDown(): void {
		// Your own additional tear down.
		parent::tearDown();
	}

	/**
	 * 権限によってsellableSymbolsを呼び出せるかどうかをテストします。
	 *
	 * @test
	 * @testdox [42FED76E][GraphQL] sellableSymbols - user: $user_type, expected: $expected
	 * @dataProvider accessDataProvider
	 */
	public function access( string $user_type, bool $expected ) {
		// リクエストを送信するユーザーを設定
		$this->getUser( $user_type )->setCurrentUser();

		$query = <<<GRAPHQL
			query {
				sellableSymbols(networkType: MAINNET)
			}
		GRAPHQL;

		// GraphQLリクエストを送信
		$data = $this->requestGraphQL( $query )->get_data();

		// 正常に取得できることを期待している条件の時
		if ( $expected ) {
			$this->assertFalse( isset( $data['errors'] ) ); // エラーフィールドは存在しない
			$sellable_symbols = $data['data']['sellableSymbols'];
			$this->assertIsArray( $sellable_symbols );    // 販売可能な通貨シンボル一覧が取得できている
		} else {
			$this->assertFalse( isset( $data['data'] ) );   // データフィールドは存在しない
			$this->assertTrue( isset( $data['errors'] ) );  // エラーフィールドが存在する
		}
	}

	public function accessDataProvider(): array {
		return array(
			// user, expected(visible or not)

			// contributor以上の権限があれば取得可能
			array( UserType::ADMINISTRATOR, true ),
			array( UserType::CONTRIBUTOR, true ),
			array( UserType::ANOTHER_CONTRIBUTOR, true ),
			array( UserType::VISITOR, false ),
		);
	}
}
