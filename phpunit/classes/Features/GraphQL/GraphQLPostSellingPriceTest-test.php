<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Features\GraphQL\RootValue;
use Cornix\Serendipity\Core\Lib\Path\ProjectFile;
use Cornix\Serendipity\Core\Lib\SystemInfo\PluginSettings;
use Cornix\Serendipity\Core\Types\Price;

require_once __DIR__ . '/GraphQLTestBase.php';


/**
 * PostSellingPriceを取得するGraphQLのテスト
 */
class GraphQLPostSellingPriceTest extends GraphQLTestBase {

	// #[\Override]
	public function setUp(): void {
		parent::setUp();
		// Your own additional setup.

		$plugin_settings_stub = $this->createMock( PluginSettings::class );
		$plugin_settings_stub->method( 'getPostSellingPrice' )->willReturn( new Price( '0x1903', get_current_user_id(), 'USD' ) );

		parent::registerGraphQLRoute( new RootValue( $plugin_settings_stub ) );

		// 寄稿者が投稿を作成
		// パラメータ: https://miya0001.github.io/wp-unit-docs/factory.html#parameters
		$this->post_ID = $this->factory->post->create( array( 'post_author' => $this->getUserId( self::CONTRIBUTOR ) ) );
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
	 * @testdox [] 公開状況やユーザーによって販売価格の取得可否が異なる
	 * @dataProvider accessDataProvider
	 */
	public function access( string $post_status, string $user_type, bool $expected ) {

		// 投稿のステータスを変更(公開、下書き、等)
		// https://developer.wordpress.org/reference/functions/wp_update_post/#user-contributed-notes
		$ret = wp_update_post(
			array(
				'ID'          => $this->post_ID,
				'post_status' => $post_status,
			)
		);
		$this->assertEquals( $ret, $this->post_ID );

		// リクエストを送信するユーザーを設定
		$this->setCurrentUser( $user_type );

		// GraphQLリクエストを送信
		$data = $this->requestGraphQL(
			json_encode(
				array(
					'query'     => file_get_contents( ( new ProjectFile( 'includes/assets/graphql/block/PostSellingInfo.graphql' ) )->toLocalPath() ),
					'variables' => array(
						'postID' => $this->post_ID,
					),
				)
			)
		)->get_data();
		// error_log( json_encode( $data, JSON_PRETTY_PRINT ) );

		// 正常に取得できることを期待している条件の時
		if ( $expected ) {
			$this->assertFalse( isset( $data['errors'] ) ); // エラーフィールドは存在しない
			$this->assertNotNull( $data['data']['postSellingPrice'] );    // 販売価格が取得できている
		} else {
			$this->assertTrue( isset( $data['errors'] ) );  // エラーフィールドが存在する
			$this->assertNull( $data['data']['postSellingPrice'] );   // 販売価格が取得できない
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

			// 非公開状態の投稿の販売価格は、投稿の作成者と管理者のみ閲覧可能
			array( 'draft', self::ADMINISTRATOR, true ),            // 管理者は非公開の投稿の販売価格を閲覧可能
			array( 'draft', self::CONTRIBUTOR, true ),              // 寄稿者は自身が作成した非公開の投稿の販売価格を閲覧可能
			array( 'draft', self::READ_ONLY_CONTRIBUTOR, false ),   // 読み取り専用寄稿者は自身が作成していない非公開の投稿の販売価格を閲覧不可
			array( 'draft', self::VISITOR, false ),                 // 訪問者は非公開の投稿の販売価格を閲覧不可
		);
	}
}
