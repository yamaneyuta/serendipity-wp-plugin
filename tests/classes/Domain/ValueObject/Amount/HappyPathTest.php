<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Test\Domain\ValueObject\Amount;

use Cornix\Serendipity\Core\Domain\ValueObject\Amount;
use Cornix\Serendipity\TestLib\PHPUnit\UnitTestCaseBase;

class HappyPathTest extends UnitTestCaseBase {

	/**
	 * 数値を::from()メソッドに渡してインスタンスを生成するテスト
	 *
	 * @test
	 * @testdox [111038CD] from() with number - $amount_text => $expected
	 * @dataProvider fromNumberTextDataProvider
	 */
	public function fromNumberText( string $amount_text, string $expected ): void {
		// ARRANGE
		// Do nothing

		// ACT
		$amount = Amount::from( $amount_text );
		// ASSERT
		$this->assertInstanceOf( Amount::class, $amount );
		$this->assertEquals( $expected, (string) $amount );
		$this->assertEquals( $expected, $amount->value() ); // valueは__toString()と同じ値を返す
	}
	public function fromNumberTextDataProvider(): array {
		return array(
			array( '42', '42' ),
			array( '0', '0' ),
			array( '-42', '-42' ),
			array( '1.23', '1.23' ),
			array( '-1.23', '-1.23' ),
			array( '1.2300', '1.23' ),	// 小数点以下の末尾が0の場合は削除される
			array( '0.0001', '0.0001' ),
			array( '-0.0001', '-0.0001' ),
		);
	}

	/**
	 * 乗算のテスト
	 *
	 * @test
	 * @testdox [8A1AD6FF] mul() - $amount1_text x $amount2_text = $expected_result_text
	 * @dataProvider mulDataProvider
	 */
	public function mul( string $amount1_text, string $amount2_text, string $expected_result_text ): void {
		// ARRANGE
		$amount1 = Amount::from( $amount1_text );
		$amount2 = Amount::from( $amount2_text );

		// ACT
		$result = $amount1->mul( $amount2 );
		// ASSERT
		$this->assertEquals( $expected_result_text, (string) $result );
	}
	public function mulDataProvider(): array {
		return array(
			array( '1', '2', '2' ),
			array( '1.5', '2', '3' ),
			array( '1.5', '2.5', '3.75' ),
			array( '-1.5', '2', '-3' ),
			array( '1.5', '-2', '-3' ),
			array( '-1.5', '-2', '3' ),
			array( '0.01', '1000', '10' ),
			array( '0.01', '0.01', '0.0001' ),
			array( '0.2', '0.5', '0.1' ), // 0.10 -> 0.1
			array( '0', '42', '0' ),
			array( '42', '0', '0' ),
			array( '0', '42.1', '0' ),
			array( '42.1', '0', '0' ),
		);
	}

	/**
	 * 除算のテスト
	 *
	 * @test
	 * @testdox [512D3E3D] div() - $amount1_text / $amount2_text (accuracy: $accuracy_decimals)= $expected_result_text
	 * @dataProvider divDataProvider
	 */
	public function div( string $amount1_text, string $amount2_text, int $accuracy_decimals, string $expected_result_text ): void {
		// ARRANGE
		$amount1 = Amount::from( $amount1_text );
		$amount2 = Amount::from( $amount2_text );

		// ACT
		$result = $amount1->div( $amount2, $accuracy_decimals );
		// ASSERT
		$this->assertEquals( $expected_result_text, (string) $result );
	}
	public function divDataProvider(): array {
		return array(
			// 正の数の除算
			array( '10', '3', 2, '3.33' ), // 割り切れないので、指定した精度までの値を返す
			array( '10', '2', 1, '5' ),
			array( '10', '2', 0, '5' ),
			array( '10', '4', 3, '2.5' ), // 3桁を指定しても割り切れるので2.5になる
			array( '100', '3', 2, '33.33' ),
			array( '100', '4', 1, '25' ),
			// 負の数の除算
			array( '-10', '3', 2, '-3.33' ),
			array( '10', '-3', 2, '-3.33' ),
			array( '-10', '-3', 2, '3.33' ),
			// 小数点を含む除算
			array( '7.5', '2.5', 1, '3' ), // 割り切れるので、整数値を返す
			array( '7.5', '2.5', 0, '3' ),
			array( '1.25', '0.5', 1, '2.5' ),
			array( '3.14159', '2', 3, '1.57' ),
			// より大きい桁数での除算
			array( '22', '7', 10, '3.1428571428' ),
			array( '1', '3', 8, '0.33333333' ),
			// 結果が整数になる除算
			array( '8', '2', 2, '4' ),
			array( '15', '3', 0, '5' ),
			array( '100', '10', 1, '10' ),
			// 0での除算（分子が0）
			array( '0', '5', 3, '0' ),
			array( '0', '10.5', 2, '0' ),
			// 小さな数値の除算
			array( '0.001', '0.1', 3, '0.01' ),
			array( '0.5', '0.25', 1, '2' ),
		);
	}
}
