<?php
declare(strict_types=1);

namespace Cornix\Serendipity\TestCase\Domain\ValueObject\Confirmations;

use Cornix\Serendipity\Core\Domain\ValueObject\Confirmations;
use Cornix\Serendipity\Test\PHPUnit\UnitTestCaseBase;

class ErrorPathTest extends UnitTestCaseBase {

	/**
	 * 無効な値を::from()メソッドに渡すテスト
	 *
	 * @test
	 * @testdox [90EA48EA] from() with invalid confirmations value - $invalid_confirmations_value
	 * @dataProvider fromInvalidValueDataProvider
	 */
	public function fromInvalidValue( $invalid_confirmations_value ): void {
		// ARRANGE
		$this->expectException( \InvalidArgumentException::class );

		// ACT(&ASSERT)
		Confirmations::from( $invalid_confirmations_value );
	}
	public function fromInvalidValueDataProvider(): array {
		return array(
			array( 0 ),
			array( -1 ),
			array( 'invalid' ),
			array( 3.14 ),
			array( '3.14' ),
			array( true ),
			array( false ),
			array( array() ),
		);
	}
}
