<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Domain\ValueObject\Amount;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;
use Cornix\Serendipity\Core\Domain\ValueObject\Price;
use Cornix\Serendipity\Core\Domain\ValueObject\Symbol;
use phpseclib\Math\BigInteger;

class PriceTest extends IntegrationTestBase {

	/**
	 * @test
	 * @testdox [62DA8EF5] Price::toTokenAmount() - 1wei
	 */
	public function toTokenAmount1wei18(): void {
		// ARRANGE
		$sut = new Price( Amount::from( '0.000000000000000001' ), new Symbol( 'ETH' ) );    // 1wei

		// ACT
		$amount = $sut->toTokenAmount( ChainID::ethMainnet() );

		// ASSERT
		$this->assertEquals( $amount->value(), '1' );
	}

	/**
	 * @test
	 * @testdox [1E3BEE15] Price::toTokenAmount() - 1ETH
	 */
	public function toTokenAmount1ETH18(): void {
		// ARRANGE
		$sut = new Price( Amount::from( '1' ), new Symbol( 'ETH' ) );   // 1ETH

		// ACT
		$amount = $sut->toTokenAmount( ChainID::ethMainnet() );

		// ASSERT
		$this->assertEquals( $amount->value(), '1000000000000000000' );
	}
}
