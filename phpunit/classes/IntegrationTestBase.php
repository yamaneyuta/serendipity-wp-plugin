<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Features\Repository\Database\DBSchema;
use Cornix\Serendipity\Core\Hooks\API\GraphQLHook;
use Cornix\Serendipity\Core\Lib\Repository\Option\Option;
use Cornix\Serendipity\Core\Lib\Rest\RestProperty;

/**
 * 結合テストの基底クラス
 */
abstract class IntegrationTestBase extends WP_UnitTestCase {

	// #[\Override]
	public function setUp(): void {
		parent::setUp();
		// Your own additional setup.

		// 寄稿者を作成
		// 作成したユーザーIDはデータベースのAuto Incrementの影響で毎回変更されるが、
		// tearDownで毎回削除される。
		// そのため、contributerの存在チェックは不要。
		$contrubuter_id = wp_create_user( 'contributor', 'password', '' );
		( new WP_User( $contrubuter_id ) )->set_role( 'contributor' );

		$another_contrubuter_id = wp_create_user( 'another_contributor', 'password', '' );
		( new WP_User( $another_contrubuter_id ) )->set_role( 'contributor' );

		// フィールドに保持
		$this->user_mapping = array(
			self::ADMINISTRATOR       => 1,
			self::CONTRIBUTOR         => $contrubuter_id,
			self::ANOTHER_CONTRIBUTOR => $another_contrubuter_id,
			self::VISITOR             => 0,
		);

		global $wp_rest_server;
		$this->server = $wp_rest_server = new WP_REST_Server();

		( new GraphQLHook( $this->crateRestPropertyStub() ) )->register();
		do_action( 'rest_api_init' );
	}

	// #[\Override]
	public function tearDown(): void {
		global $wp_rest_server;
		$wp_rest_server = null;

		// Your own additional tear down.
		parent::tearDown();
	}

	/**
	 * 本プラグイン用のテーブル及びOptionを初期化します。
	 *
	 * - 通常の結合テストの場合: setUpメソッド内で引数無しで呼び出す
	 * - 各データベースに対してクエリのテストを行うような場合: 各テストメソッド内で引数を指定して呼び出す
	 */
	protected function initializeDatabase( wpdb $wpdb = null ): void {
		// 引数がnullの場合は、$wpdbをグローバル変数から取得
		$wpdb = $wpdb ?? $GLOBALS['wpdb'];

		// wp-envが準備したmysqlに接続する場合は、プラグイン用のOptionを削除
		// (それ以外のDBの場合、delete_option呼び出しが機能しないため、処理が不要)
		if ( $wpdb->dbhost === $GLOBALS['wpdb']->dbhost ) {
			// プラグイン用Optionを削除
			( new Option() )->uninstall();
		}

		// 本プラグイン用のテーブルを再作成
		$dbSchema = new DBSchema( $wpdb );
		$dbSchema->uninstall();
		$dbSchema->migrate();
	}


	// dataProviderでフィールドの値が取得できない(setUp前に呼ばれる)ため、マッピング用の定数を定義
	// これらの定数をユーザー種別(user_type)として扱う。値は重複しなければ何でも(数値等でも)良いが、ここでは文字列を使用する。
	protected const ADMINISTRATOR       = 'ADMINISTRATOR';
	protected const CONTRIBUTOR         = 'CONTRIBUTOR';
	protected const ANOTHER_CONTRIBUTOR = 'ANOTHER_CONTRIBUTOR';
	protected const VISITOR             = 'VISITOR';

	/** @var array<string,int> */
	private $user_mapping;

	private function crateRestPropertyStub(): RestProperty {
		$rest_property_stub = $this->createMock( RestProperty::class );
		$rest_property_stub->method( 'namespace' )->willReturn( 'phpunit' );    // テスト用の名前空間
		$rest_property_stub->method( 'graphQLRoute' )->willReturn( ( new RestProperty() )->graphQLRoute() ); // こちらは変更しない
		return $rest_property_stub;
	}

	protected function requestGraphQL( string $json ): WP_REST_Response {
		$rest_property = $this->crateRestPropertyStub();
		$namespace     = $rest_property->namespace();
		$graphQLRoute  = $rest_property->graphQLRoute();
		$request       = new WP_REST_Request( 'POST', "/${namespace}${graphQLRoute}" );

		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( $json );

		/** @var WP_REST_Response */
		$response = $this->server->dispatch( $request );

		return $response;
	}

	/**
	 * ユーザー種別からユーザーIDを取得します。
	 *
	 * @param string $user_type ユーザー種別 (self::ADMINISTRATOR, self::CONTRIBUTOR, self::ANOTHER_CONTRIBUTOR, self::VISITOR)
	 * @return int ユーザーID
	 */
	protected function getUserId( string $user_type ): int {
		return $this->user_mapping[ $user_type ];
	}

	/**
	 * GraphQLをリクエストするユーザーを切り替えます。
	 *
	 * @param string $user_type ユーザー種別 (self::ADMINISTRATOR, self::CONTRIBUTOR, self::ANOTHER_CONTRIBUTOR, self::VISITOR)
	 */
	protected function setCurrentUser( string $user_type ): void {
		// 引数`0`で`wp_set_current_user`を呼び出しても、IDが`0`のユーザーオブジェクトが返ってくるため、
		// `wp_set_current_user`の戻り値チェックは行わない。
		wp_set_current_user( $this->getUserId( $user_type ) );
	}

	/** @var WP_REST_Server */
	private $server;
}
