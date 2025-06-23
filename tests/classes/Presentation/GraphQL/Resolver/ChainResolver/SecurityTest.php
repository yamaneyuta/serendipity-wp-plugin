<?php

declare(strict_types=1);

namespace Cornix\Serendipity\TestCase\Presentation\GraphQL\Resolver\ChainResolver;

use Cornix\Serendipity\Core\Constant\ChainIdValue;
use Cornix\Serendipity\Core\Infrastructure\Logging\ValueObject\LogLevel;
use Cornix\Serendipity\Test\Entity\WpUser;
use Cornix\Serendipity\Test\PHPUnit\UnitTestCaseBase;

class SecurityTest extends UnitTestCaseBase {

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		self::resetDatabase(); // データベースをリセット
	}

	public function setUp(): void {
		parent::setUp();
		$this->setAppLogLevel( LogLevel::none() );  // ログを抑制
	}

	/**
	 * chain はQueryに定義されていないためアクセス不可
	 *
	 * @test
	 * @testdox [B3EBB418] Cannot query field "chain" on type "Query". - $user
	 * @dataProvider inaccessibleDataProvider
	 */
	public function inaccessible( WpUser $user ): void {
		// ARRANGE
		$chain_id = ChainIdValue::PRIVATENET_L1;

		// ACT
		$result = $this->graphQl( $user )->request(
			<<<GRAPHQL
				query Chain(\$chainId: Int!) {
					chain(chainId: \$chainId) {
						id
					}
				}
			GRAPHQL,
			array( 'chainId' => $chain_id )
		)->get_data();

		// ASSERT
		$this->assertEquals( 1, count( $result['errors'] ) );
		$this->assertEquals( 'Cannot query field "chain" on type "Query".', $result['errors'][0]['message'] );
	}
	public function inaccessibleDataProvider(): array {
		return array(
			array( WpUser::admin() ),
			array( WpUser::contributor() ),
			array( WpUser::visitor() ),
		);
	}


	/**
	 * chain はMutationにも定義されていないためアクセス不可
	 *
	 * @test
	 * @testdox [E99054B1] Cannot query field "chain" on type "Mutation". - $user
	 * @dataProvider inaccessibleMutationDataProvider
	 */
	public function inaccessibleMutation( WpUser $user ): void {
		// ARRANGE
		$chain_id = ChainIdValue::PRIVATENET_L1;

		// ACT
		$result = $this->graphQl( $user )->request(
			<<<GRAPHQL
				mutation Chain(\$chainId: Int!) {
					chain(chainId: \$chainId) {
						id
					}
				}
			GRAPHQL,
			array( 'chainId' => $chain_id )
		)->get_data();

		// ASSERT
		$this->assertEquals( 1, count( $result['errors'] ) );
		$this->assertStringContainsString( 'Cannot query field "chain" on type "Mutation".', $result['errors'][0]['message'] );
	}
	public function inaccessibleMutationDataProvider(): array {
		return array(
			array( WpUser::admin() ),
			array( WpUser::contributor() ),
			array( WpUser::visitor() ),
		);
	}
}
