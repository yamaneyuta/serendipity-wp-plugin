<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Test\PHPUnit;

use Cornix\Serendipity\Core\Infrastructure\DI\ContainerDefinitions;
use Cornix\Serendipity\Test\Entity\WpUser;
use Cornix\Serendipity\Test\Service\ClientRequestService;
use DI\Container;
use DI\ContainerBuilder;
use WP_UnitTestCase;

/** 基本的なユニットテストケース */
class UnitTestCaseBase extends WP_UnitTestCase {

	/** @inheritdoc */
	public function setUp(): void {
		parent::setUp();

		// DIコンテナの初期化
		$this->container = ( new InitializeContainer() )->handle();

		// クライアントリクエストサービスの初期化
		$this->client_request_service = new ClientRequestService( $this->container );
		$this->client_request_service->setUp();
	}

	/** @inheritdoc */
	public function tearDown(): void {
		parent::tearDown();

		$this->client_request_service->tearDown();
	}

	private ?Container $container;
	protected function container(): Container {
		return $this->container;
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
