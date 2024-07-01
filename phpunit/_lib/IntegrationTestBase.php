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

		// プラグイン用Optionを削除
		// ※ $wpdbの参照先が`tests-mysql`以外であっても、スキーマバージョンは`tests-mysql`の
		// optionsを参照しているのでOptionテーブルの初期化も必要
		( new Option() )->uninstall();

		// 本プラグイン用のテーブルを再作成
		$dbSchema = new DBSchema( $wpdb );
		$dbSchema->uninstall();
		$dbSchema->migrate();

		$wpdb->query( 'COMMIT;' );
	}

	protected function administator() {
		return TestUserFactory::craeteAdministrator();
	}

	protected function contributor() {
		return TestUserFactory::createContributor();
	}

	protected function another_contributor() {
		return TestUserFactory::createAnotherContributor();
	}

	protected function visitor() {
		return TestUserFactory::createVisitor();
	}

	private function crateRestPropertyStub(): RestProperty {
		$rest_property_stub = $this->createMock( RestProperty::class );
		$rest_property_stub->method( 'namespace' )->willReturn( 'phpunit' );    // テスト用の名前空間
		$rest_property_stub->method( 'graphQLRoute' )->willReturn( ( new RestProperty() )->graphQLRoute() ); // こちらは変更しない
		return $rest_property_stub;
	}

	protected function requestGraphQL( string $query, array $variables = null ): WP_REST_Response {

		$request_data = array(
			'query' => $query,
		);
		if ( $variables ) {
			$request_data['variables'] = $variables;
		}

		$rest_property = $this->crateRestPropertyStub();
		$namespace     = $rest_property->namespace();
		$graphQLRoute  = $rest_property->graphQLRoute();
		$request       = new WP_REST_Request( 'POST', "/${namespace}${graphQLRoute}" );

		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( json_encode( $request_data ) );

		/** @var WP_REST_Response */
		$response = $this->server->dispatch( $request );

		return $response;
	}

	/** @var WP_REST_Server */
	private $server;
}

/**
 * @internal
 */
class TestUserFactory {
	public static function craeteAdministrator(): TestUser {
		return new TestUser( 1 );
	}

	public static function createContributor(): TestUser {
		return new TestUser( self::createContributorIfNotExists( 'contributor' ) );
	}

	public static function createAnotherContributor(): TestUser {
		return new TestUser( self::createContributorIfNotExists( 'another_contributor' ) );
	}

	public static function createVisitor(): TestUser {
		return new TestUser( 0 );
	}

	private static function createContributorIfNotExists( string $name ): int {
		$user = get_user_by( 'login', $name );
		if ( $user ) {
			return $user->ID;
		} else {
			// ユーザーを作成し、権限に`contributor`を設定
			$id = wp_create_user( $name, 'password' );
			( new WP_User( $id ) )->set_role( 'contributor' );
			return $id;
		}
	}
}

class TestUser {
	public function __construct( int $id ) {
		$this->id = $id;
	}
	private int $id;

	public function setCurrentUser(): void {
		// 引数`0`で`wp_set_current_user`を呼び出しても、IDが`0`のユーザーオブジェクトが返ってくるため、
		// `wp_set_current_user`の戻り値チェックは行わない。
		wp_set_current_user( $this->id );
	}

	public function createPost(): int {
		if ( ! user_can( $this->id, 'edit_posts' ) ) {
			// 投稿を作成する権限がないエラー
			throw new Exception( '[2DD19278] You do not have permission to create a post. id: ' . $this->id );
		}

		// パラメータ: https://miya0001.github.io/wp-unit-docs/factory.html#parameters
		return ( new class() extends WP_UnitTestCase {
			public function createPost( array $args ) {
				return $this->factory()->post->create( $args );
			}
		} )->createPost( array( 'post_author' => $this->id ) );
	}

	public function __toString() {
		if ( 0 === $this->id ) {
			return 'visitor';
		}
		return get_user_by( 'ID', $this->id )->user_login;
	}
}
