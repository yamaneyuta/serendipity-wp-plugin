<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Repository\PostSetting;
use Cornix\Serendipity\Core\Types\PostSettingType;
use Cornix\Serendipity\Core\Types\PriceType;

/**
 * postSettingに対してアクセスできないことを確認するテスト
 *
 * ※ `RootValue.php`で`postSetting`フィールドがResolverとして登録されているが、
 *    GraphQLの定義ではQueryに登録されていないため、アクセスできないことを確認する
 */
class GraphQLPostSettingTest extends IntegrationTestBase {

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
	 * @test
	 * @testdox [41FBDDF6][GraphQL] postSetting - user: $user_type, query_type: $query_type
	 * @dataProvider accessDataProvider
	 */
	public function access( string $user_type, int $query_type ) {
		// リクエストを送信するユーザーを設定
		$this->getUser( $user_type )->setCurrentUser();

		// 寄稿者が投稿を作成
		$post_ID = $this->getUser( UserType::CONTRIBUTOR )->createPost();
		// 投稿の設定を保存
		global $wpdb;
		$postSetting = new PostSettingType( new PriceType( '0x123456', 18, 'ETH' ) );
		( new PostSetting( $wpdb ) )->set( $post_ID, $postSetting );

		// 投稿のステータスを公開に変更
		$ret = wp_update_post(
			array(
				'ID'          => $post_ID,
				'post_status' => 'publish',
			)
		);
		$this->assertEquals( $ret, $post_ID );

		// テスト用のクエリを取得
		$query_args = $this->getQueryAndVariables( $query_type, $post_ID );

		// GraphQLリクエストを送信
		$data = $this->requestGraphQL( $query_args[0], $query_args[1] )->get_data();

		$this->assertTrue( isset( $data['errors'] ) );  // エラーフィールドが存在する
		$this->assertFalse( isset( $data['data'] ) );   // 設定が取得できない
		// エラーメッセージが正しいことを確認
		$this->assertEquals( $data['errors'][0]['message'], 'Cannot query field "postSetting" on type "Query".' );
	}

	private function getQueryAndVariables( int $query_type, int $post_ID ): array {
		switch ( $query_type ) {
			case 1:
				$query     = <<<GRAPHQL
					query PostSetting(\$postID: Int!) {
						postSetting(postID: \$postID) {
							sellingPrice {
								amountHex
								decimals
								symbol
							}
						}
					}
				GRAPHQL;
				$variables = array(
					'postID' => $post_ID,
				);
				break;
			case 2:
				$query     = <<<GRAPHQL
					query {
						postSetting
					}
				GRAPHQL;
				$variables = null;
				break;
			default:
				throw new Exception( "[78BEA0EC] Invalid query type. - query_type: $query_type" );
		}

		return array( $query, $variables );
	}

	public function accessDataProvider(): array {

		return array(
			// user, query_type

			array( UserType::ADMINISTRATOR, 1 ),
			array( UserType::CONTRIBUTOR, 1 ),
			array( UserType::ANOTHER_CONTRIBUTOR, 1 ),
			array( UserType::VISITOR, 1 ),

			array( UserType::ADMINISTRATOR, 2 ),
			array( UserType::CONTRIBUTOR, 2 ),
			array( UserType::ANOTHER_CONTRIBUTOR, 2 ),
			array( UserType::VISITOR, 2 ),
		);
	}
}
