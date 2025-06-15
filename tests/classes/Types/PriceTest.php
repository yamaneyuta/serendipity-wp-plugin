<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Infrastructure\Format\HexFormat;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;
use Cornix\Serendipity\Core\Domain\ValueObject\Price;
use phpseclib\Math\BigInteger;

class PriceTest extends IntegrationTestBase {

	/**
	 * @test
	 * @testdox [62DA8EF5] Price::toTokenAmount() - 1wei(decimals:18)
	 */
	public function toTokenAmount1wei18(): void {
		// ARRANGE
		$sut = new Price( '0x01', 18, 'ETH' );    // 1wei

		// ACT
		$amount_hex = $sut->toTokenAmount( ChainID::ethMainnet() );
		$amount_dec = ( new BigInteger( $amount_hex, 16 ) )->toString();

		// ASSERT
		$this->assertEquals( $amount_dec, '1' );
	}

	/**
	 * @test
	 * @testdox [1E3BEE15] Price::toTokenAmount() - 1ETH(decimals:18)
	 */
	public function toTokenAmount1ETH18(): void {
		// ARRANGE
		$sut = new Price( HexFormat::toHex( new BigInteger( 10 ** 18 ) ), 18, 'ETH' );   // 1ETH

		// ACT
		$amount_hex = $sut->toTokenAmount( ChainID::ethMainnet() );
		$amount_dec = ( new BigInteger( $amount_hex, 16 ) )->toString();

		// ASSERT
		$this->assertEquals( $amount_dec, '1000000000000000000' );
	}

	/**
	 * @test
	 * @testdox [41D19499] Price::toTokenAmount() - 0.01ETH(decimals:2)
	 */
	public function toTokenAmount001ETH2(): void {
		// ARRANGE
		$sut = new Price( '0x01', 2, 'ETH' ); // 0.01ETH

		// ACT
		$amount_hex = $sut->toTokenAmount( ChainID::ethMainnet() );
		$amount_dec = ( new BigInteger( $amount_hex, 16 ) )->toString();

		// ASSERT
		$this->assertEquals( $amount_dec, '10000000000000000' );
	}

	/**
	 * @test
	 * @testdox [D9C8B5CD] Price::toTokenAmount() - 0.01ETH(decimals:18)
	 */
	public function toTokenAmount001ETH18(): void {
		// ARRANGE
		$sut = new Price( HexFormat::toHex( new BigInteger( 10 ** 16 ) ), 18, 'ETH' );   // 0.01ETH

		// ACT
		$amount_hex = $sut->toTokenAmount( ChainID::ethMainnet() );
		$amount_dec = ( new BigInteger( $amount_hex, 16 ) )->toString();

		// ASSERT
		$this->assertEquals( $amount_dec, '10000000000000000' );
	}

	/**
	 * @test
	 * @testdox [5762D46C] Price::toTokenAmount() - 1wei(decimals:20)
	 */
	public function toTokenAmount1wei20(): void {
		// ARRANGE
		$sut = new Price( '0x64', 20, 'ETH' );    // 1wei

		// ACT
		$amount_hex = $sut->toTokenAmount( ChainID::ethMainnet() );
		$amount_dec = ( new BigInteger( $amount_hex, 16 ) )->toString();

		// ASSERT
		$this->assertEquals( $amount_dec, '1' );
	}

	/**
	 * @test
	 * @testdox [C89377E0] Price::toTokenAmount() - 1ETH(decimals:20)
	 */
	public function toTokenAmount1ETH20(): void {
		// ARRANGE
		$sut = new Price( HexFormat::toHex( new BigInteger( '100000000000000000000' ) ), 20, 'ETH' );    // 1ETH

		// ACT
		$amount_hex = $sut->toTokenAmount( ChainID::ethMainnet() );
		$amount_dec = ( new BigInteger( $amount_hex, 16 ) )->toString();

		// ASSERT
		$this->assertEquals( $amount_dec, '1000000000000000000' );
	}
}
