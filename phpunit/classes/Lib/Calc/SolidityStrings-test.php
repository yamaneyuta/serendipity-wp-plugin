<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Calc\SolidityStrings;
use Cornix\Serendipity\Core\Lib\Web3\Ethers;
use phpseclib\Math\BigInteger;

class SolidityStringsTest extends WP_UnitTestCase {

	/**
	 * 数値をHEXに変換するテスト
	 *
	 * @test
	 * @testdox [AB395174] SolidityStrings::valueToHexString() - $value -> $expected
	 * @dataProvider valueToHexStringDataProvider
	 */
	public function valueToHexString( $value, string $expected ): void {
		// ARRANGE
		// Do nothing.

		// ACT
		$ret = SolidityStrings::valueToHexString( $value );

		// ASSERT
		$this->assertEquals( $ret, $expected );
		$this->assertEquals( strlen( $ret ) % 2, 0 );     // 結果は偶数桁
		$this->assertRegExp( '/^0x[0-9a-f]+$/', $ret ); // 16進数文字列はすべて小文字
	}
	public function valueToHexStringDataProvider(): array {
		return array(
			array( 1, '0x01' ),
			array( '0x1', '0x01' ),
			array( '0x1234', '0x1234' ),
			array( '0x12345', '0x012345' ),
			array( '0x', '0x00' ),
			array( new BigInteger( 1, 10 ), '0x01' ),
		);
	}

	/**
	 * 数値をHEXに変換するテスト(不正なデータ)
	 *
	 * @test
	 * @testdox [FFABADA9] SolidityStrings::valueToHexString() - value: 'invalid'
	 */
	public function valueToHexStringInvalidData(): void {
		// ARRANGE
		$value = 'invalid';

		// ACT, ASSERT
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( '[8C48698E]' );
		SolidityStrings::valueToHexString( $value );
	}


	/**
	 * アドレスをHEXに変換するテスト
	 *
	 * @test
	 * @testdox [30A2439D] SolidityStrings::addressToHexString() - $address -> $expected
	 * @dataProvider addressToHexStringDataProvider
	 */
	public function addressToHexString( $address, string $expected ): void {
		// ARRANGE
		// Do nothing.

		// ACT
		$ret = SolidityStrings::addressToHexString( $address );

		// ASSERT
		$this->assertEquals( $ret, $expected );
		$this->assertRegExp( '/^0x[0-9a-f]{40}$/', $ret );  // 16進数文字列はすべて小文字
	}
	public function addressToHexStringDataProvider(): array {
		return array(
			array( '0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266', '0xf39fd6e51aad88f6f4ce6ab8827279cfffb92266' ), // 通常のアドレス(小文字に変換される)
			array( Ethers::zeroAddress(), '0x0000000000000000000000000000000000000000' ),
			array( '0x0', '0x0000000000000000000000000000000000000000' ),
			array( '0xA', '0x000000000000000000000000000000000000000a' ), // 小文字になる
		);
	}

	/**
	 * アドレスをHEXに変換するテスト(不正なデータ)
	 *
	 * @test
	 * @testdox [1997F132] SolidityStrings::addressToHexString() - address: 'invalid'
	 */
	public function addressToHexStringInvalidData(): void {
		// ARRANGE
		$address = 'invalid';

		// ACT, ASSERT
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( '[A862D0B5]' );
		SolidityStrings::addressToHexString( $address );
	}
}
