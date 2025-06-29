<?php

declare(strict_types=1);

namespace Cornix\Serendipity\Test\Presentation\GraphQL\Resolver\ChainsResolver;

use Cornix\Serendipity\Core\Infrastructure\Logging\ValueObject\LogLevel;
use Cornix\Serendipity\TestLib\Entity\WpUser;

class SecurityTest extends ChainsResolverTestBase {

	public function setUp(): void {
		parent::setUp();
		$this->setAppLogLevel( LogLevel::none() ); // ログを抑制
	}

	/**
	 * 管理者以外はchainsの呼び出しができないことを確認します。
	 *
	 * @test
	 * @testdox [FB1380D1] Cannot query field "chains" on type "Query". - $user
	 * @dataProvider cannotQueryChainsDataProvider
	 */
	public function testCannotQueryChains( WpUser $user ): void {
		// Arrange
		// Do nothing

		// Act
		$result = $this->graphQl( $user )->request(
			self::CHAINS_SIMPLE_QUERY,
			array(
				'filter' => array(
					'chainID' => self::CONNECTABLE_CHAIN_ID,
				),
			)
		)->get_data();

		// Assert
		$this->assertCount( 1, $result['errors'] ); // エラーが発生していること
		$this->assertEquals( 'Internal server error', $result['errors'][0]['message'] );
		$this->assertEquals( 'chains', $result['errors'][0]['path'][0] ); // エラーのパスが 'chains' であること
	}
	public function cannotQueryChainsDataProvider(): array {
		return array(
			array( WpUser::contributor() ),
			array( WpUser::visitor() ),
		);
	}
}
