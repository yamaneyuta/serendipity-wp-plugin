<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Repository\WidgetAttributes\WidgetAttributes;
use Cornix\Serendipity\Core\Types\NetworkCategory;

/**
 * sellingPriceを取得するGraphQLのテスト
 */
class GraphQLSellingPriceTest extends IntegrationTestBase {

	// #[\Override]
	public function setUp(): void {
		parent::setUp();
		// Your own additional setup.
	}

	// #[\Override]
	public function tearDown(): void {
		// Your own additional tear down.
		parent::tearDown();
	}

	/**
	 * @test
	 * @testdox [94E323B2][GraphQL] sellingPrice - post_status: $post_status, user: $user_type, expected: $expected
	 * @dataProvider accessDataProvider
	 */
	public function access( string $post_status, string $user_type, bool $expected ) {
		// リクエストを送信するユーザーを設定
		$this->getUser( $user_type )->setCurrentUser();

		// 寄稿者が投稿を作成
		$post_ID = $this->getUser( UserType::CONTRIBUTOR )->createPost(
			array(
				'post_content' => $this->createTestPostContent(
					WidgetAttributes::from( NetworkCategory::mainnet(), '0x123456', 18, 'ETH' )
				),
			)
		);

		// 投稿のステータスを変更(公開、下書き、等)
		// https://developer.wordpress.org/reference/functions/wp_update_post/#user-contributed-notes
		$ret = wp_update_post(
			array(
				'ID'          => $post_ID,
				'post_status' => $post_status,
			)
		);
		$this->assertEquals( $ret, $post_ID );

		$query     = <<<GRAPHQL
			query SellingPrice(\$postID: Int!) {
				sellingPrice(postID: \$postID) {
					amountHex
					decimals
					symbol
				}
			}
		GRAPHQL;
		$variables = array(
			'postID' => $post_ID,
		);

		// GraphQLリクエストを送信
		$data = $this->requestGraphQL( $query, $variables )->get_data();

		// 正常に取得できることを期待している条件の時
		if ( $expected ) {
			$this->assertFalse( isset( $data['errors'] ) ); // エラーフィールドは存在しない
			$selling_price = $data['data']['sellingPrice'];
			$this->assertEquals(
				array(
					'amountHex' => '0x123456',
					'decimals'  => 18,
					'symbol'    => 'ETH',
				),
				$selling_price
			);  // 登録した販売価格が取得できている
		} else {
			$this->assertTrue( isset( $data['errors'] ) );  // エラーフィールドが存在する
			$this->assertNull( $data['data']['sellingPrice'] );   // 設定が取得できない
		}
	}

	public function accessDataProvider(): array {

		return array(
			// post_status, user, expected(visible or not)

			// 公開状態の投稿の販売価格は誰でも閲覧可能
			array( 'publish', UserType::ADMINISTRATOR, true ),
			array( 'publish', UserType::CONTRIBUTOR, true ),
			array( 'publish', UserType::ANOTHER_CONTRIBUTOR, true ),
			array( 'publish', UserType::VISITOR, true ),

			// 下書き(非公開状態)の投稿の販売価格は、投稿の作成者と管理者のみ閲覧可能
			array( 'draft', UserType::ADMINISTRATOR, true ),          // 管理者は非公開の投稿の販売価格を閲覧可能
			array( 'draft', UserType::CONTRIBUTOR, true ),           // 寄稿者は自身が作成した非公開の投稿の販売価格を閲覧可能
			array( 'draft', UserType::ANOTHER_CONTRIBUTOR, false ),  // 他の寄稿者は自身が作成していない非公開の投稿の販売価格を閲覧不可
			array( 'draft', UserType::VISITOR, false ),              // 訪問者は非公開の投稿の販売価格を閲覧不可
		);
	}
}
