<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Repository\Definition\NativeTokenSymbolDefinition;
use Cornix\Serendipity\Core\Lib\Security\Judge;

class NativeTokenSymbolDefinitionTest extends WP_UnitTestCase {
	/**
	 * 処理のテストではなく、実装漏れの確認を行うためのテスト
	 * すべてのチェーンIDでネイティブトークンのシンボルが定義されているかどうかをチェックする
	 *
	 * @test
	 * @testdox [58A17BA6] NativeTokenSymbolDefinition::get - chainID: $chain_ID
	 * @dataProvider getDataProvider
	 */
	public function get( int $chain_ID ) {
		// ARRANGE
		// Do nothing

		// ACT
		$native_symbol = ( new NativeTokenSymbolDefinition() )->get( $chain_ID );

		// ASSERT
		$this->assertTrue( Judge::isSymbol( $native_symbol ) );
	}
	public function getDataProvider(): array {
		return array_map(
			fn( int $chain_ID ) => array( $chain_ID ),
			( new TestAllChainID() )->get()
		);
	}

	/**
	 * チェーンIDが不正な場合のテスト
	 *
	 * @test
	 * @testdox [4333F684] NativeTokenSymbolDefinition::get - invalid chainID
	 */
	public function getWithInvalidChainID() {
		// ARRANGE
		$invalid_chain_ID = PHP_INT_MAX;    // 無効なチェーンID
		$this->expectExceptionMessage( '[398C040E]' );  // 例外が発生することを確認

		// ACT
		( new NativeTokenSymbolDefinition() )->get( $invalid_chain_ID );

		// ASSERT
		// Do nothing
	}
}
