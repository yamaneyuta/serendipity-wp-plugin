<?php
declare(strict_types=1);

namespace Cornix\Serendipity\TestCase\ValueObject;

use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Test\PHPUnit\UnitTestCaseBase;

class AddressTest extends UnitTestCaseBase {

	/**
	 * 有効なアドレスをAddressインスタンスに変換できることを確認
	 *
	 * @test
	 * @testdox [14522287] Address::from() - valid address: $address_value
	 * @dataProvider validAddressProvider
	 */
	public function testValidAddress( string $address_value ) {
		// ARRANGE
		// Do nothing

		// ACT & ASSERT
		$this->assertInstanceOf( Address::class, Address::from( $address_value ) );
	}
	public function validAddressProvider() {
		return array_map(
			fn( $val ) => array( $val ),
			array(
				'0x0000000000000000000000000000000000000000', // ゼロアドレス
				'0xf39fd6e51aad88f6f4ce6ab8827279cfffb92266', // チェックサムアドレス
				'0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266', // すべて小文字のアドレス
			)
		);
	}

	/**
	 * アドレスにnullを渡すとインスタンスは生成されずにnullが返されることを確認
	 *
	 * @test
	 * @testdox [ABF47440] Address::from() - address: null
	 */
	public function testFromNull() {
		// ARRANGE
		// Do nothing

		// ACT & ASSERT
		$this->assertNull( Address::from( null ) );
	}

	/**
	 * 無効なアドレスをAddressインスタンスに変換しようとすると例外が発生することを確認
	 *
	 * @test
	 * @testdox [7BEE8739] Address::from() - invalid address: $invalid_address_value
	 * @dataProvider invalidAddressProvider
	 */
	public function testInvalidAddress( string $invalid_address_value ) {
		// ARRANGE
		// Do nothing

		// ACT & ASSERT
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( '[B4AE59FC]' );
		Address::from( $invalid_address_value );
	}
	public function invalidAddressProvider() {
		return array_map(
			fn( $val ) => array( $val ),
			array(
				'0xF39Fd6e51aad88F6F4ce6aB8827279cffFb92266',   // 不正なチェックサム(先頭のfが大文字になっている)
				'0xF39Fd6e51aad88F6F4ce6aB8827279cffFb9226',    // 桁数が足りないチェックサムアドレス
				'0xf39Fd6e51aad88F6F4ce6aB8827279cffFb9226',    // 桁数が足りない全て小文字のアドレス
				'f39Fd6e51aad88F6F4ce6aB8827279cffFb92266',     // 先頭の0xがない
			)
		);
	}
}
