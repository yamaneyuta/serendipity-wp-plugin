<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Features\Migration\DBSchema;
use Cornix\Serendipity\Core\Features\Uninstall\OptionUninstaller;
use Cornix\Serendipity\Core\Hooks\API\GraphQLHook;
use Cornix\Serendipity\Core\Lib\Rest\RestProperty;
use Cornix\Serendipity\Core\Lib\Web3\Blockchain;

require_once __DIR__ . '/Web3/TestRPCUrl.php';

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

		// Hardhatの初期化
		( new HardhatController() )->setUp();
	}

	// #[\Override]
	public function tearDown(): void {
		// hardhatのリセット
		( new HardhatController() )->tearDown();

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
		( new OptionUninstaller() )->execute();

		// 本プラグイン用のテーブルを再作成
		$dbSchema = new DBSchema( $wpdb );
		$dbSchema->uninstall();
		$dbSchema->migrate();

		$wpdb->query( 'COMMIT;' );
	}

	public function getUser( string $user_type ): TestUser {
		return new TestUser( $user_type );
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


class UserType {
	public const ADMINISTRATOR       = 'admin';  // ユーザー名は`admin`
	public const CONTRIBUTOR         = 'contributor';
	public const ANOTHER_CONTRIBUTOR = 'another_contributor';
	public const VISITOR             = 'visitor';
}

class TestUser {

	public function __construct( string $user_type ) {
		// テスト用のメソッド内(setUpが終わって)から呼び出されるようにしてください。
		// dataProvider(setUpの前に呼び出される)の中では呼び出せません。
		// (`wp_users`テーブルとの整合性が取れなくなります。)
		assert( false !== get_user_by( 'ID', 1 ), '[3291193C] administrator user not found' );

		// $user_typeはUserTypeのプロパティであること
		$properties = ( new ReflectionClass( UserType::class ) )->getConstants();
		assert( in_array( $user_type, array_values( $properties ) ), '[AAF3AE09] invalid user_type: ' . $user_type );

		$this->initialize( $user_type );
	}

	private string $username;
	private int $id;

	public function id(): int {
		return $this->id;
	}

	private function initialize( string $user_type ) {
		if ( $user_type === UserType::VISITOR ) {
			$this->id = 0;
			return;
		}

		$this->username = $user_type;   // ユーザー名はユーザー種別
		$user           = get_user_by( 'login', $this->username );

		if ( false === $user ) {
			switch ( $user_type ) {
				case UserType::CONTRIBUTOR:
				case UserType::ANOTHER_CONTRIBUTOR:
					// 投稿権限を持つユーザーを作成
					$this->id = wp_create_user( $this->username, 'password' );
					( new WP_User( $this->id ) )->set_role( 'contributor' );
					break;

				default:
					throw new Exception( '[E19F2AED] invalid user_type: ' . $user_type );
			}
			return;
		} else {
			$this->id = $user->ID;
			return;
		}
	}

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

	public function __toString(): string {
		return $this->username;
	}
}


/**
 * Hardhatを操作するためのクラス
 *
 * @internal
 */
class HardhatController {
	public function __construct() {
		$testRPCUrl     = new TestRPCUrl();
		$this->rpc_urls = array(
			$testRPCUrl->privatenetL1(),
			$testRPCUrl->privatenetL2(),
		);

		$this->initialize();
	}

	private static array $is_ready  = array();
	private static array $snapshots = array();

	/** @var string[] */
	private array $rpc_urls;

	public function setUp(): void {
		$this->snapshot();
	}

	public function tearDown(): void {
		$this->restoreSnapshot();
	}

	private function initialize(): void {
		// Hardhatのデプロイが完了するまで待機
		foreach ( $this->rpc_urls as $rpc_url ) {
			// 初期化済みの場合は改めてチェックしない
			if ( isset( self::$is_ready[ $rpc_url ] ) ) {
				break;
			}

			// ネットワークが利用可能になるまで待機
			$this->waitForNetworkReady( $rpc_url );

			// コントラクトが利用可能になるまで待機
			$this->waitForContractReady( $rpc_url );

			// 初期化済みであることをマーク
			self::$is_ready[ $rpc_url ] = true;
		}
	}

	private function snapshot(): void {
		foreach ( $this->rpc_urls as $rpc_url ) {
			$id                          = ( new Hardhat( $rpc_url ) )->snapshot();
			self::$snapshots[ $rpc_url ] = $id;
		}
	}
	private function restoreSnapshot(): void {
		foreach ( $this->rpc_urls as $rpc_url ) {
			// スナップショットIDを取得
			$id = self::$snapshots[ $rpc_url ];
			assert( is_string( $id ), '583B6FD4' );
			// スナップショットを復元
			$success = ( new Hardhat( $rpc_url ) )->revert( $id );

			assert( $success );
			unset( self::$snapshots[ $rpc_url ] );  // 使用済みのスナップショットIDを破棄
		}
	}

	private function waitForNetworkReady( string $rpc_url ) {
		// cURLでステータス200が取得できるまで最大60秒待機
		for ( $i = 0; $i < 60; $i++ ) {
			$response = wp_remote_get( $rpc_url );
			$code     = wp_remote_retrieve_response_code( $response );
			if ( 200 === $code ) {
				return;
			}
			error_log( "[78AC2176] Wait for ready(network). $rpc_url, code: $code" );
			sleep( 1 );
		}
		throw new Exception( "[A9AA734C] Hardhat is not ready. $rpc_url" );
	}

	private function waitForContractReady( string $rpc_url ) {
		// コントラクトデプロイ後、特定のアドレスの残高が増えるので、それを確認するまで待機
		$blockchain = new Blockchain( $rpc_url );
		for ( $i = 0; $i < 60; $i++ ) {
			$balance_hex = $blockchain->getBalanceHex( ( new HardhatAccount() )->marker() );
			if ( hexdec( $balance_hex ) > 0 ) {
				return;
			}
			error_log( "[CC842103] Wait for ready(contract). $rpc_url" );
			sleep( 1 );
		}

		throw new Exception( "[44663EC9] Hardhat is not ready. $rpc_url" );
	}
}
