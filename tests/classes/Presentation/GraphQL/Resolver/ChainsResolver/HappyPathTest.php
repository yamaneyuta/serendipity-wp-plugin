<?php

declare(strict_types=1);

namespace Cornix\Serendipity\TestCase\Presentation\GraphQL\Resolver\ChainsResolver;

use Cornix\Serendipity\Test\Entity\WpUser;

class HappyPathTest extends ChainsResolverTestBase {

	/**
	 * 存在するチェーンIDを指定して、チェーン一覧を取得できることを確認します。
	 *
	 * @test
	 * @testdox [5EB1AAF0] get chains by existing chain ID
	 */
	public function testGetChainsByExistingId(): void {
		// Arrange
		// Do nothing

		// Act
		$result = $this->graphQl( WpUser::admin() )->request(
			self::CHAINS_SIMPLE_QUERY,
			array(
				'filter' => array(
					'chainID' => self::CONNECTABLE_CHAIN_ID,
				),
			)
		)->get_data();

		// Assert
		$this->assertNull( $result['errors'] ); // エラーがないこと
		$this->assertCount( 1, $result['data']['chains'] ); // 1件のチェーンが返されること
		$this->assertEquals( self::CONNECTABLE_CHAIN_ID, $result['data']['chains'][0]['id'] ); // 指定したチェーンIDが返されること
	}


	/**
	 * 接続可能なチェーン一覧を取得するテスト
	 *
	 * @test
	 * @testdox [1444A975] get connectable chains
	 */
	public function testGetConnectableChains(): void {
		// Arrange
		// Do nothing

		// Act
		$result = $this->graphQl( WpUser::admin() )->request(
			self::CHAINS_SIMPLE_QUERY,
			array(
				'filter' => array(
					'isConnectable' => true,
				),
			)
		)->get_data();

		// Assert
		$this->assertNull( $result['errors'] ); // エラーがないこと
		$this->assertNotEmpty( $result['data']['chains'] ); // チェーンが返されること
		$this->assertContains( self::CONNECTABLE_CHAIN_ID, array_column( $result['data']['chains'], 'id' ) ); // 接続可能なチェーンIDが含まれること
		$this->assertNotContains( self::NOT_CONNECTABLE_CHAIN_ID, array_column( $result['data']['chains'], 'id' ) ); // 接続できないチェーンIDが含まれないこと
	}


	/**
	 * 接続できないチェーン一覧を取得するテスト
	 *
	 * @test
	 * @testdox [B0B1F37C] get chains by non-connectable filter
	 */
	public function testGetChainsByNonConnectableFilter(): void {
		// Arrange
		// Do nothing

		// Act
		$result = $this->graphQl( WpUser::admin() )->request(
			self::CHAINS_SIMPLE_QUERY,
			array(
				'filter' => array(
					'isConnectable' => false,
				),
			)
		)->get_data();

		// Assert
		$this->assertNull( $result['errors'] ); // エラーがないこと
		$this->assertNotEmpty( $result['data']['chains'] ); // チェーンが返されること
		$this->assertContains( self::NOT_CONNECTABLE_CHAIN_ID, array_column( $result['data']['chains'], 'id' ) ); // 接続できないチェーンIDが含まれること
		$this->assertNotContains( self::CONNECTABLE_CHAIN_ID, array_column( $result['data']['chains'], 'id' ) ); // 接続可能なチェーンIDが含まれないこと
	}


	/**
	 * 結果が0件になる条件でチェーン一覧を取得するテスト
	 *
	 * @test
	 * @testdox [6A334F3F] get chains with no results - chain_id: $chain_id, is_connectable: $is_connectable
	 * @dataProvider getChainsWithNoResultsDataProvider
	 */
	public function testGetChainsWithNoResults( int $chain_id, ?bool $is_connectable ): void {
		// Arrange
		// Do nothing

		// Act
		$result = $this->graphQl( WpUser::admin() )->request(
			self::CHAINS_SIMPLE_QUERY,
			array(
				'filter' => array(
					'chainID'       => $chain_id,
					'isConnectable' => $is_connectable,
				),
			)
		)->get_data();

		// Assert
		$this->assertNull( $result['errors'] ); // エラーがないこと
		$this->assertCount( 0, $result['data']['chains'] ); // チェーンが返されないこと
	}
	public function getChainsWithNoResultsDataProvider(): array {
		return array(
			// chain_id, is_connectable
			array( self::CONNECTABLE_CHAIN_ID, false ),
			array( self::NOT_CONNECTABLE_CHAIN_ID, true ),
			array( self::INVALID_CHAIN_ID, false ),
			array( self::INVALID_CHAIN_ID, true ),
			array( self::INVALID_CHAIN_ID, null ),
		);
	}

	/**
	 * フルノードのチェーン情報を取得するテスト
	 *
	 * @test
	 * @testdox [63BB5B93] get full node chain information
	 */
	public function testFullNodeQuery(): void {
		// Arrange
		// Do nothing

		// Act
		$result = $this->graphQl( WpUser::admin() )->request(
			self::CHAINS_FULL_QUERY,
			array(
				'filter' => array(
					// フィルタ無し
				),
			)
		)->get_data();

		// Assert
		$this->assertNull( $result['errors'] ); // エラーがないこと
	}
}
