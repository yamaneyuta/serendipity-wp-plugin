<?php
declare(strict_types=1);

/**
 * isPrivatenetEnabledプロパティにアクセスするテスト
 */
class GraphQLIsPrivatenetEnabledTest extends IntegrationTestBase {

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
	 * isPrivatenetEnabledプロパティにアクセスするテスト
	 *
	 * @test
	 * @testdox [3130E613][GraphQL] isPrivatenetEnabled - user: $user_type, expected: $expected
	 * @dataProvider accessDataProvider
	 */
	public function isPrivatenetEnabled( string $user_type, bool $expected ) {
		// リクエストを送信するユーザーを設定
		$this->getUser( $user_type )->setCurrentUser();

		$query = <<<GRAPHQL
			query IsPrivatenetEnabled {
				isPrivatenetEnabled
			}
		GRAPHQL;

		// GraphQLリクエストを送信
		$data = $this->requestGraphQL( $query )->get_data();

		// レスポンスの検証
		$is_privaatenet_enabled = $data['data']['isPrivatenetEnabled'];
		$this->assertEquals( $expected, $is_privaatenet_enabled );
	}

	public function accessDataProvider(): array {
		return array(
			// 投稿編集の権限がある場合は価格設定時にプライベートネットに接続できるかどうかを取得する必要があるため、true
			array( UserType::ADMINISTRATOR, true ),
			array( UserType::CONTRIBUTOR, true ),
			array( UserType::ANOTHER_CONTRIBUTOR, true ),
			// Visitorはプライベートネットに接続できるかどうかは知る必要がないため、false
			array( UserType::VISITOR, false ),
		);
	}
}
