<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Code\NetworkTypeCode;
use Cornix\Serendipity\Core\Lib\Repository\PostSetting;

/**
 * setPostSettingを呼び出すGraphQLのテスト
 */
class GraphQLSetPostSettingTest extends IntegrationTestBase {

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
	 * 本テストクラスで使用するGraphQLクエリを取得します。
	 *
	 * @return string
	 */
	private function getQuery(): string {
		return <<<GRAPHQL
			mutation SetPostSetting(\$postID: Int!, \$postSetting: PostSettingInput!) {
				setPostSetting(postID: \$postID, postSetting: \$postSetting)
			}
		GRAPHQL;
	}

	/**
	 * 本テストクラスで使用するGraphQLに渡す変数を取得します。
	 */
	private function getVariables( int $post_ID, string $amount_hex, int $decimals, string $symbol ): array {
		return array(
			'postID'      => $post_ID,
			'postSetting' => array(
				'sellingPrice'   => array(
					'amountHex' => $amount_hex,
					'decimals'  => $decimals,
					'symbol'    => $symbol,
				),
				'sellingNetwork' => NetworkTypeCode::MAINNET,
			),
		);
	}

	/**
	 * @test
	 * @testdox [B1EB0D97][GraphQL] setPostSetting - post_status: $post_status, user: $user_type, expected: $expected
	 * @dataProvider accessDataProvider
	 */
	public function access( string $post_status, string $user_type, bool $expected ) {
		// リクエストを送信するユーザーを設定
		$this->getUser( $user_type )->setCurrentUser();

		// 寄稿者が投稿を作成
		$post_ID = $this->getUser( UserType::CONTRIBUTOR )->createPost();

		// 投稿のステータスを変更(公開、下書き、等)
		// https://developer.wordpress.org/reference/functions/wp_update_post/#user-contributed-notes
		$ret = wp_update_post(
			array(
				'ID'          => $post_ID,
				'post_status' => $post_status,
			)
		);
		$this->assertEquals( $ret, $post_ID );

		// ユーザーが発行するGraphQLリクエスト
		$query = $this->getQuery();
		// GraphQL発行時に渡す変数(数量、小数点以下の桁数、通貨記号は適当に指定)
		$variables = $this->getVariables( $post_ID, '0xf3f77d06', 18, 'ETH' );

		// GraphQLリクエストを送信
		$data = $this->requestGraphQL( $query, $variables )->get_data();

		// 正常に取得できることを期待している条件の時
		if ( $expected ) {
			$this->assertFalse( isset( $data['errors'] ) ); // エラーフィールドは存在しない
			$result = $data['data']['setPostSetting'];
			$this->assertTrue( $result );  // 設定が保存されている

			// 保存された設定を取得
			global $wpdb;
			$saved_post_setting = ( new PostSetting( $wpdb ) )->get( $post_ID );
			$this->assertNotNull( $saved_post_setting );    // 設定が取得できている
			$selling_price = $saved_post_setting->sellingPrice;

			$this->assertEquals( '0xf3f77d06', $selling_price->amountHex );
			$this->assertEquals( 18, $selling_price->decimals );
			$this->assertEquals( 'ETH', $selling_price->symbol );
		} else {
			$this->assertTrue( isset( $data['errors'] ) );  // エラーフィールドが存在する
			$this->assertNull( $data['data']['setPostSetting'] );   // 結果が取得できない

			// 設定が保存されていないことを確認
			global $wpdb;
			$saved_post_setting = ( new PostSetting( $wpdb ) )->get( $post_ID );
			$this->assertNull( $saved_post_setting );    // 設定が取得できていない
		}
	}

	public function accessDataProvider(): array {

		return array(
			// post_status, user, expected(visible or not)

			// 公開状態で設定できるのは編集可能なユーザーのみ
			array( 'publish', UserType::ADMINISTRATOR, true ),
			array( 'publish', UserType::CONTRIBUTOR, false ),   // 公開済みの投稿は寄稿者は設定不可(投稿の編集ができないため)
			array( 'publish', UserType::ANOTHER_CONTRIBUTOR, false ),
			array( 'publish', UserType::VISITOR, false ),

			// 下書き(非公開状態)の投稿設定は、投稿の作成者と管理者のみ設定可能
			array( 'draft', UserType::ADMINISTRATOR, true ),          // 管理者は非公開の投稿の設定を設定可能
			array( 'draft', UserType::CONTRIBUTOR, true ),           // 寄稿者は自身が作成した非公開の投稿の設定を設定可能(下書きの段階では投稿の編集は可能なため)
			array( 'draft', UserType::ANOTHER_CONTRIBUTOR, false ),  // 他の寄稿者は自身が作成していない非公開の投稿の設定を設定不可
			array( 'draft', UserType::VISITOR, false ),              // 訪問者は非公開の投稿の設定を設定不可
		);
	}

	/**
	 * 不正な値を設定
	 *
	 * @test
	 * @testdox [B1EB0D97][GraphQL] setPostSetting - amount_hex: $amount_hex, decimals: $decimals, symbol: $symbol
	 * @dataProvider invalidValueDataProvider
	 * @return void
	 */
	public function invalidValue( string $amount_hex, int $decimals, string $symbol ): void {
		// 管理者で投稿を作成
		$admin = $this->getUser( UserType::ADMINISTRATOR );
		$admin->setCurrentUser();
		$post_ID   = $admin->createPost();
		$query     = $this->getQuery();
		$variables = $this->getVariables( $post_ID, $amount_hex, $decimals, $symbol );

		$data = $this->requestGraphQL( $query, $variables )->get_data();

		$this->assertTrue( isset( $data['errors'] ) );  // エラーフィールドが存在する
		$this->assertNull( $data['data']['setPostSetting'] );   // 結果が取得できない(trueでない)
	}

	public function invalidValueDataProvider(): array {
		return array(
			// amount_hex, decimals, symbol
			array( 'foobar', 0, 'ETH' ), // amountHexが不正な値
			array( '0x1234', -1, 'ETH' ), // decimalsが不正な値
			array( '0x1234', 0, '$' ), // symbolが不正な値
		);
	}
}
