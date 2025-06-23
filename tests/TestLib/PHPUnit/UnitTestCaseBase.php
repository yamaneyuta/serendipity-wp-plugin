<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Test\PHPUnit;

use Cornix\Serendipity\Core\Infrastructure\Database\MySQLiFactory;
use Cornix\Serendipity\Core\Infrastructure\DI\ContainerDefinitions;
use Cornix\Serendipity\Core\Infrastructure\Logging\LogLevelProvider;
use Cornix\Serendipity\Core\Infrastructure\Logging\ValueObject\LogCategory;
use Cornix\Serendipity\Core\Infrastructure\Logging\ValueObject\LogLevel;
use Cornix\Serendipity\Core\Presentation\PluginUpdateHook;
use Cornix\Serendipity\Core\Repository\Name\Prefix;
use Cornix\Serendipity\Test\Entity\WpUser;
use Cornix\Serendipity\Test\Service\ClientRequestService;
use DI\Container;
use DI\ContainerBuilder;
use WP_UnitTestCase;
use wpdb;

/** 基本的なユニットテストケース */
class UnitTestCaseBase extends WP_UnitTestCase {


	/** @inheritdoc */
	public function setUp(): void {
		parent::setUp();

		// ログレベルの設定(テストのため最小限のログ出力とする)
		// ※ optionsテーブルの値のためsetUpBeforeClassではなく、setUpで設定
		$this->log_level_provider = self::container()->get( LogLevelProvider::class );
		$this->log_level_provider->setLogLevel( LogCategory::app(), LogLevel::error() );
		$this->log_level_provider->setLogLevel( LogCategory::audit(), LogLevel::none() );

		// クライアントリクエストサービスの初期化
		$this->client_request_service = new ClientRequestService( self::container() );
		$this->client_request_service->setUp();
	}

	/** @inheritdoc */
	public function tearDown(): void {
		parent::tearDown();

		$this->client_request_service->tearDown();
	}

	/**
	 * データベースをリセットします
	 * データベースにアクセスするテストを行う場合は`setUp`もしくは`setUpBeforeClass`で呼び出してください。
	 */
	protected static function resetDatabase(): void {
		( new ResetDatabase() )->handle( $GLOBALS['wpdb'] );
	}

	private static ?Container $container = null;
	protected static function container(): Container {
		if ( null === self::$container ) {
			self::$container = ( new InitializeContainer() )->handle();
		}
		return self::$container;
	}

	private LogLevelProvider $log_level_provider;
	protected function setAppLogLevel( LogLevel $level ): void {
		$this->log_level_provider->setLogLevel( LogCategory::app(), $level );
	}

	private ClientRequestService $client_request_service;
	protected function graphQl( WpUser $user = null ) {
		return $this->client_request_service->createGraphQlRequester( $user );
	}


	// ----- PHPUnitの差異を吸収 -----

	/**
	 * Add assertMatchesRegularExpression() method for phpunit >= 8.0 < 9.0 for compatibility with PHP 7.2.
	 *
	 * @see https://github.com/sebastianbergmann/phpunit/issues/4174
	 */
	public static function assertMatchesRegularExpression( string $pattern, string $string, string $message = '' ): void {
		if ( method_exists( parent::class, 'assertMatchesRegularExpression' ) ) {
			/** @disregard P1013 Undefined method */
			parent::assertMatchesRegularExpression( $pattern, $string, $message );
		} else {
			parent::assertRegExp( $pattern, $string, $message );
		}
	}
	/** @deprecated use assertMatchesRegularExpression() instead. */
	public static function assertRegExp( string $pattern, string $string, string $message = '' ): void {
		// assertRegExpは新しいPHPUnitでは非推奨のため、ここでは例外を投げるように変更。
		// (強制的にassertMatchesRegularExpressionを使用させるため)
		throw new \Exception( '[8BC03F79] assertRegExp is deprecated. Please use assertMatchesRegularExpression.' );
	}
}


/** DIコンテナのセットアップを行います */
class InitializeContainer {
	public function handle(): Container {
		$containerBuilder = new ContainerBuilder();
		$containerBuilder->addDefinitions( ContainerDefinitions::getDefinitions() );
		return $containerBuilder->build();
	}
}

class ResetDatabase {
	public function handle( wpdb $wpdb ): void {
		// テーブル、オプション、トランジェントをすべて削除
		$this->deletePluginData( $wpdb );

		// データベースの初期化処理を実行
		$this->initializeDatabase();
	}

	/** このプラグインが作成したテーブル及びoptionテーブルに存在するデータをすべて削除します */
	private function deletePluginData( wpdb $wpdb ): void {
		$prefix           = new Prefix();
		$table_prefix     = $prefix->tableNamePrefix();
		$option_prefix    = $prefix->optionKeyPrefix();
		$transient_prefix = $prefix->transientKeyPrefix();

		// テーブルをすべて削除するクエリを構築
		$cleanup_query = implode(
			'',
			array_map(
				fn( $table_name ) => "DROP TABLE IF EXISTS `{$table_name}`;",
				$wpdb->get_col( "SHOW TABLES LIKE '{$table_prefix}%'" )
			)
		);

		// option, transient を雑に削除するクエリを構築
		$cleanup_query .= "DELETE FROM `{$wpdb->options}` WHERE option_name LIKE '{$option_prefix}%';";
		$cleanup_query .= "DELETE FROM `{$wpdb->options}` WHERE option_name LIKE '_transient_%{$transient_prefix}%';";

		$mysqli = ( new MySQLiFactory() )->create( $wpdb );
		$result = $mysqli->multi_query( 'START TRANSACTION;' . $cleanup_query . 'COMMIT;' );
		if ( false === $result ) {
			throw new \RuntimeException( '[1DE69773] Failed to drop tables. ' . $mysqli->error . " Query: {$cleanup_query}" );
		}
	}

	private function initializeDatabase(): void {
		// admin_initの代わりにPluginUpdateHookを呼び出す
		// ⇒Optionsテーブルが初期化されているので、プラグインの初期インストール処理が実行される
		$current_screen = get_current_screen();
		set_current_screen( 'index.php' );
		( new PluginUpdateHook() )->addActionAdminInit();
		set_current_screen( $current_screen ?? '' );
	}
}
