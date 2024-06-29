<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Repository\Database\PostSetting;
use Cornix\Serendipity\Core\Types\PostSettingType;
use Cornix\Serendipity\Core\Types\PriceType;

require_once __DIR__ . '/../../IntegrationTestBase.php';


/**
 * PostSettingを取得するGraphQLのテスト
 */
class GraphQLPostSettingTest extends IntegrationTestBase {

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


	/** @var int 寄稿者(contributor)が作成した投稿のID */
	private $post_ID;


	/**
	 * @test
	 * @testdox [48447663] 公開状況やユーザーによって販売価格の取得可否が異なる
	 * @dataProvider accessDataProvider
	 */
	public function access( string $post_status, string $user_type, bool $expected ) {
		// 寄稿者が投稿を作成
		// パラメータ: https://miya0001.github.io/wp-unit-docs/factory.html#parameters
		$this->post_ID = $this->factory->post->create( array( 'post_author' => $this->getUserId( self::CONTRIBUTOR ) ) );
		// 投稿の設定を保存
		global $wpdb;
		$postSetting = new PostSettingType( new PriceType( '0x123456', 18, 'ETH' ) );
		( new PostSetting( $wpdb ) )->set( $this->post_ID, $postSetting );

		// 投稿のステータスを変更(公開、下書き、等)
		// https://developer.wordpress.org/reference/functions/wp_update_post/#user-contributed-notes
		$ret = wp_update_post(
			array(
				'ID'          => $this->post_ID,
				'post_status' => $post_status,
			)
		);
		$this->assertEquals( $ret, $this->post_ID );

		$query = <<<GRAPHQL
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

		// リクエストを送信するユーザーを設定
		$this->setCurrentUser( $user_type );

		// GraphQLリクエストを送信
		$data = $this->requestGraphQL(
			json_encode(
				array(
					'query'     => $query,
					'variables' => array(
						'postID' => $this->post_ID,
					),
				)
			)
		)->get_data();

		// 正常に取得できることを期待している条件の時
		if ( $expected ) {
			$this->assertFalse( isset( $data['errors'] ) ); // エラーフィールドは存在しない
			$post_setting = $data['data']['postSetting'];
			$this->assertNotNull( $post_setting );    // 設定が取得できている
			$selling_price = $post_setting['sellingPrice'];
			$this->assertNotNull( $selling_price );   // 販売価格が取得できている
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
			$this->assertNull( $data['data']['postSetting'] );   // 設定が取得できない
		}
	}

	public function accessDataProvider(): array {
		return array(
			// post_status, user_type, expected(visible or not)

			// 公開状態の投稿の販売価格は誰でも閲覧可能
			array( 'publish', self::ADMINISTRATOR, true ),
			array( 'publish', self::CONTRIBUTOR, true ),
			array( 'publish', self::READ_ONLY_CONTRIBUTOR, true ),
			array( 'publish', self::VISITOR, true ),

			// 下書き(非公開状態)の投稿の販売価格は、投稿の作成者と管理者のみ閲覧可能
			array( 'draft', self::ADMINISTRATOR, true ),            // 管理者は非公開の投稿の販売価格を閲覧可能
			array( 'draft', self::CONTRIBUTOR, true ),              // 寄稿者は自身が作成した非公開の投稿の販売価格を閲覧可能
			array( 'draft', self::READ_ONLY_CONTRIBUTOR, false ),   // 読み取り専用寄稿者は自身が作成していない非公開の投稿の販売価格を閲覧不可
			array( 'draft', self::VISITOR, false ),                 // 訪問者は非公開の投稿の販売価格を閲覧不可
		);
	}
}
