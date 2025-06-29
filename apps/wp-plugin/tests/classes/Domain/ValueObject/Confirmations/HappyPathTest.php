<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Test\Domain\ValueObject\Confirmations;

use Cornix\Serendipity\Core\Domain\ValueObject\Confirmations;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockTag;
use Cornix\Serendipity\TestLib\PHPUnit\UnitTestCaseBase;

class HappyPathTest extends UnitTestCaseBase {

	/**
	 * 数値を::from()メソッドに渡してインスタンスを生成するテスト
	 *
	 * @test
	 * @testdox [E13FFAFC] from() with number
	 */
	public function fromNumber(): void {
		// ARRANGE
		$confirmations_value = 42;
		// ACT
		$confirmations = Confirmations::from( $confirmations_value );
		// ASSERT
		$this->assertInstanceOf( Confirmations::class, $confirmations );
		$this->assertIsInt( $confirmations->value() );
		$this->assertEquals( 42, $confirmations->value() );
		$this->assertEquals( '42', (string) $confirmations );
	}

	/**
	 * 数字の文字列を::from()メソッドに渡してインスタンスを生成するテスト
	 *
	 * @test
	 * @testdox [387EEA6F] from() with string number
	 */
	public function fromStringNumber(): void {
		// ARRANGE
		$confirmations_value = '42';
		// ACT
		$confirmations = Confirmations::from( $confirmations_value );
		// ASSERT
		$this->assertInstanceOf( Confirmations::class, $confirmations );
		$this->assertIsInt( $confirmations->value() );
		$this->assertEquals( 42, $confirmations->value() ); // valueはint型になっている
		$this->assertEquals( '42', (string) $confirmations );
	}

	/**
	 * ブロックタグを::from()メソッドに渡してインスタンスを生成するテスト
	 *
	 * @test
	 * @testdox [992C77C9] from() with block tag
	 */
	public function fromBlockTag(): void {
		// ARRANGE
		$confirmations_value = 'latest';
		// ACT
		$confirmations = Confirmations::from( $confirmations_value );
		// ASSERT
		$this->assertInstanceOf( Confirmations::class, $confirmations );
		$this->assertInstanceOf( BlockTag::class, $confirmations->value() );
		$this->assertEquals( BlockTag::latest(), $confirmations->value() );
		$this->assertEquals( 'latest', (string) $confirmations->value() ); // valueはBlockTagオブジェクトが返ってくる
		$this->assertEquals( 'latest', (string) $confirmations );
	}

	/**
	 * nullを::from()メソッドに渡すテスト
	 *
	 * @test
	 * @testdox [740D5F65] from() with null
	 */
	public function fromNull(): void {
		// ARRANGE
		$confirmations_value = null;
		// ACT
		$confirmations = Confirmations::from( $confirmations_value );
		// ASSERT
		$this->assertNull( $confirmations );
	}

	/**
	 * equals()メソッドのテスト
	 *
	 * @test
	 * @testdox [68260468] equals() - $confirmations_value1, $confirmations_value2, $expected_result
	 * @dataProvider equalsDataProvider
	 */
	public function equals( $confirmations_value1, $confirmations_value2, bool $expected_result ): void {
		// ARRANGE
		$confirmations1 = Confirmations::from( $confirmations_value1 );
		$confirmations2 = Confirmations::from( $confirmations_value2 );
		// ACT
		$result = $confirmations1->equals( $confirmations2 );
		// ASSERT
		$this->assertEquals( $expected_result, $result );
	}
	public function equalsDataProvider(): array {
		return array(
			array( 42, 42, true ),
			array( 42, '42', true ),
			array( 'latest', 'latest', true ),
			array( 42, 43, false ),
			array( 'latest', 42, false ),
		);
	}
}
