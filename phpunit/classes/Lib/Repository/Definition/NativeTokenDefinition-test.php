<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Repository\Definition\NativeTokenDefinition;
use Cornix\Serendipity\Core\Lib\Security\Judge;

class NativeTokenDefinitionTest extends WP_UnitTestCase {
	/**
	 * 処理のテストではなく、実装漏れの確認を行うためのテスト
	 * すべてのチェーンIDでネイティブトークンのシンボルが定義されているかどうかをチェックする
	 *
	 * @test
	 * @testdox [58A17BA6] NativeTokenDefinition::getSymbol - chainID: $chain_ID
	 * @dataProvider getDataProvider
	 */
	public function getSymbol( int $chain_ID ) {
		// ARRANGE
		// Do nothing

		// ACT
		$native_symbol = ( new NativeTokenDefinition() )->getSymbol( $chain_ID );

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
	 * チェーンIDが不正な場合の通貨シンボル取得テスト
	 *
	 * @test
	 * @testdox [4333F684] NativeTokenDefinition::getSymbol - invalid chainID
	 */
	public function getSymbolWithInvalidChainID() {
		// ARRANGE
		$invalid_chain_ID = PHP_INT_MAX;    // 無効なチェーンID
		$this->expectExceptionMessage( '[398C040E]' );  // 例外が発生することを確認

		// ACT
		( new NativeTokenDefinition() )->getSymbol( $invalid_chain_ID );

		// ASSERT
		// Do nothing
	}


	/**
	 * 処理のテストではなく、実装漏れの確認を行うためのテスト
	 * すべてのチェーンIDでネイティブトークンの小数点以下桁数が定義されているかどうかをチェックする
	 *
	 * @test
	 * @testdox [5D83E426] NativeTokenDefinition::getDecimals - chainID: $chain_ID
	 * @dataProvider getDataProvider
	 */
	public function getDecimals( int $chain_ID ) {
		// ARRANGE
		// Do nothing

		// ACT
		$native_decimals = ( new NativeTokenDefinition() )->getDecimals( $chain_ID );

		// ASSERT
		$this->assertTrue( Judge::isDecimals( $native_decimals ) );
	}


	/**
	 * チェーンIDが不正な場合の小数点以下桁数取得テスト
	 *
	 * @test
	 * @testdox [40DB72AC] NativeTokenDefinition::getDecimals - invalid chainID
	 */
	public function getDecimalsWithInvalidChainID() {
		// ARRANGE
		$invalid_chain_ID = PHP_INT_MAX;    // 無効なチェーンID
		$this->expectExceptionMessage( '[2ADC7FBE]' );  // 例外が発生することを確認

		// ACT
		( new NativeTokenDefinition() )->getDecimals( $invalid_chain_ID );

		// ASSERT
		// Do nothing
	}
}
