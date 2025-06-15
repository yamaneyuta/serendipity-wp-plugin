<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Infrastructure\Format\HexFormat;
use Cornix\Serendipity\Core\Repository\RateData;
use Cornix\Serendipity\Core\Domain\ValueObject\Rate;
use Cornix\Serendipity\Core\Domain\ValueObject\SymbolPair;
use Cornix\Serendipity\Core\Lib\Calc\PriceExchange;
use Cornix\Serendipity\Core\Infrastructure\Database\TableGateway\TokenTable;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;
use Cornix\Serendipity\Core\Domain\Entity\Token;
use Cornix\Serendipity\Core\Application\Service\OracleService;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\Price;
use phpseclib\Math\BigInteger;

class PriceExchangeTest extends IntegrationTestBase {

	/** @var PriceExchange */
	private $sut;
	private $rate_data_stub;
	private $oracle_stub;

	public function setUp(): void {
		parent::setUp();

		$this->rate_data_stub = $this->createMock( RateData::class );
		$this->oracle_stub    = $this->createMock( OracleService::class );
		$this->sut            = new PriceExchange( $this->rate_data_stub, $this->oracle_stub );

		// ERC20トークンの情報をテーブルに保存
		$token_table = new TokenTable( $GLOBALS['wpdb'] );
		$token_table->save( new Token( ChainID::ethMainnet(), new Address( '0x0D8775F648430679A709E98d2b0Cb6250d2887EF' ), 'BAT', 18, true ) );
		$token_table->save( new Token( ChainID::ethMainnet(), new Address( '0x514910771AF9Ca656af840dff83E8264EcF986CA' ), 'LINK', 18, true ) );
		$token_table->save( new Token( ChainID::ethMainnet(), new Address( '0xA0b86991c6218b36c1d19D4a2e9Eb0cE3606eB48' ), 'USDC', 6, true ) );

		// $this->rate_data_mockのgetメソッドを任意の引数に対して任意の戻り値を返すように設定
		$this->rate_data_stub->method( 'get' )->willReturnCallback(
			function ( SymbolPair $symbol_pair ) {
				$base  = $symbol_pair->base();
				$quote = $symbol_pair->quote();

				if ( $base === 'ETH' && $quote === 'USD' ) {
					// 1ETH=2000USDとする。decimalsは本番環境と同じ8桁
					return new Rate( $symbol_pair, HexFormat::toHex( new BigInteger( '200000000000', 10 ) ), 8 );
				} elseif ( $base === 'JPY' && $quote === 'USD' ) {
					// 1JPY=0.01USDとする。decimalsは本番環境と同じ8桁
					return new Rate( $symbol_pair, HexFormat::toHex( new BigInteger( '1000000', 10 ) ), 8 );
				} elseif ( $base === 'USDC' && $quote === 'USD' ) {
					// 1USDC=1USDとする。decimalsは本番環境と同じ8桁
					return new Rate( $symbol_pair, HexFormat::toHex( new BigInteger( '100000000', 10 ) ), 8 );
				} elseif ( $base === 'LINK' && $quote === 'ETH' ) {
					// 1LINK=0.005ETH(=10USD)とする。decimalsは本番環境と同じ18桁
					return new Rate( $symbol_pair, HexFormat::toHex( new BigInteger( '5000000000000000', 10 ) ), 18 );
				} elseif ( $base === 'BAT' && $quote === 'ETH' ) {
					// 1BAT=0.00005ETH(=0.1USD)とする。decimalsは本番環境と同じ18桁
					return new Rate( $symbol_pair, HexFormat::toHex( new BigInteger( '50000000000000', 10 ) ), 18 );
				}

				return null;
			}
		);

		$this->oracle_stub->method( 'connectableChainIDs' )->willReturnCallback(
			function ( SymbolPair $symbol_pair ) {
				// レートが取得できる時はOracleに定義されていることにする(本来は逆の関係だが、テストなので問題ない)
				return is_null( $this->rate_data_stub->get( $symbol_pair ) ) ? array() : array( ChainID::ethMainnet() );
			}
		);
	}

	/**
	 * 変換元の価格が0の時、変換後の価格も0であることを確認
	 *
	 * @test
	 * @testdox [03742C85] PriceExchange::convert - convert zero price (0ETH->USD)
	 */
	public function convertZeroPrice(): void {
		// ARRANGE
		$price = new Price( '0x0', 18, 'ETH' );

		// ACT
		$ret = $this->sut->convert( $price, 'USD' );

		// ASSERT
		$this->assertTrue( HexFormat::isZero( $ret->amountHex() ) );
		$this->assertEquals( $ret->amountHex(), $price->amountHex() );
		$this->assertEquals( $ret->decimals(), $price->decimals() );
		$this->assertNotEquals( $ret->symbol(), $price->symbol() );
		$this->assertEquals( $ret->symbol(), 'USD' );
	}

	/**
	 * 変換先が同じ通貨シンボルの場合は同じ値のまま取得できることを確認
	 *
	 * @test
	 * @testdox [1FFCE1AE] PriceExchange::convert - convert to same symbol (ETH->ETH)
	 */
	public function convertToSameSymbol(): void {
		// ARRANGE
		$price = new Price( '0x1', 1, 'ETH' );  // 0.1ETH

		// ACT
		$ret = $this->sut->convert( $price, 'ETH' );

		// ASSERT
		$this->assertEquals( $ret->amountHex(), $price->amountHex() );
		$this->assertEquals( $ret->decimals(), $price->decimals() );
		$this->assertEquals( $ret->symbol(), $price->symbol() );
		$this->assertEquals( $ret->amountHex(), $price->amountHex() );
		$this->assertEquals( $ret->toTokenAmount( ChainID::ethMainnet() ), HexFormat::toHex( new BigInteger( '100000000000000000', 10 ) ) );
	}

	/**
	 * BAT->ETH(OracleにBAT/ETHが存在するためレートの値を1回掛けることで変換可能)
	 *
	 * @test
	 * @testdox [28F3E01F] PriceExchange::convert - convert directly (BAT->ETH)
	 */
	public function convertBATtoETH(): void {
		// ARRANGE
		$price = new Price( HexFormat::toHex( new BigInteger( '2000', 10 ) ), 0, 'BAT' );  // 0.1ETHに相当する2000BAT

		// ACT
		$ret = $this->sut->convert( $price, 'ETH' );

		// ASSERT
		$this->assertEquals( $ret->symbol(), 'ETH' );
		$this->assertEquals( $ret->toTokenAmount( ChainID::ethMainnet() ), HexFormat::toHex( new BigInteger( '100000000000000000', 10 ) ) );
	}

	/**
	 * USD->EHT(OracleにETH/USDが存在するため逆数を1回掛けることで変換可能)
	 *
	 * @test
	 * @testdox [9ED1D69D] PriceExchange::convert - convert directly (USD->ETH)
	 */
	public function convertUSDtoETH(): void {
		// ARRANGE
		$price = new Price( HexFormat::toHex( new BigInteger( '20000', 10 ) ), 2, 'USD' );  // 200.00USD(=0.1ETH)

		// ACT
		$ret = $this->sut->convert( $price, 'ETH' );

		// ASSERT
		$this->assertEquals( $ret->symbol(), 'ETH' );
		$this->assertEquals( $ret->toTokenAmount( ChainID::ethMainnet() ), HexFormat::toHex( new BigInteger( '100000000000000000', 10 ) ) );
	}

	/**
	 * JPY->ETH(OracleにUSD/JPY, ETH/USDが存在するためUSDを経由して変換可能)
	 *
	 * @test
	 * @testdox [A3D3D3D3] PriceExchange::convert - convert via USD (JPY->ETH)
	 */
	public function convertJPYtoETH(): void {
		// ARRANGE
		$price = new Price( HexFormat::toHex( new BigInteger( '100000', 10 ) ), 0, 'JPY' );  // 100000JPY=1000USD=0.5ETH

		// ACT
		$ret = $this->sut->convert( $price, 'ETH' );

		// ASSERT
		$this->assertEquals( $ret->symbol(), 'ETH' );
		$this->assertEquals( $ret->toTokenAmount( ChainID::ethMainnet() ), HexFormat::toHex( new BigInteger( '500000000000000000', 10 ) ) );
	}

	/**
	 * LINK->BAT(OracleにLINK/ETH, BAT/ETHが存在するためETHを経由して変換可能)
	 *
	 * @test
	 * @testdox [88FE0087] PriceExchange::convert - convert via ETH (LINK->BAT)
	 */
	public function convertLINKtoBAT(): void {
		// ARRANGE
		$price = new Price( HexFormat::toHex( new BigInteger( '1', 10 ) ), 0, 'LINK' );  // 1LINK=100BAT

		// ACT
		$ret = $this->sut->convert( $price, 'BAT' );

		// ASSERT
		$this->assertEquals( $ret->symbol(), 'BAT' );
		$this->assertEquals( $ret->toTokenAmount( ChainID::ethMainnet() ), HexFormat::toHex( new BigInteger( '100000000000000000000', 10 ) ) );
	}

	/**
	 * LINK->USDC(OracleにLINK/ETH, ETH/USD, USDC/USDが存在するためETH及びUSDを経由して変換可能)
	 *
	 * @test
	 * @testdox [33E82C30] PriceExchange::convert - convert via ETH and USD (LINK->USDC)
	 */
	public function convertLINKtoUSDC(): void {
		// ARRANGE
		$price = new Price( HexFormat::toHex( new BigInteger( '1', 10 ) ), 0, 'LINK' );  // 1LINK=10USDC

		// ACT
		$ret = $this->sut->convert( $price, 'USDC' );

		// ASSERT
		$this->assertEquals( $ret->symbol(), 'USDC' );
		$this->assertEquals( $ret->toTokenAmount( ChainID::ethMainnet() ), HexFormat::toHex( new BigInteger( '10000000', 10 ) ) );// USDCのdecimalsは6
	}

	/**
	 * USDC->LINK(OracleにUSDC/USD, ETH/USD, LINK/ETHが存在するためUSD及びETHを経由して変換可能)
	 *
	 * @test
	 * @testdox [0555B770] PriceExchange::convert - convert via USD and ETH (USDC->LINK)
	 */
	public function convertUSDCtoLINK(): void {
		// ARRANGE
		$price = new Price( HexFormat::toHex( new BigInteger( '1', 10 ) ), 0, 'USDC' );  // 1USDC=0.1LINK

		// ACT
		$ret = $this->sut->convert( $price, 'LINK' );

		// ASSERT
		$this->assertEquals( $ret->symbol(), 'LINK' );
		$this->assertEquals( $ret->toTokenAmount( ChainID::ethMainnet() ), HexFormat::toHex( new BigInteger( '100000000000000000', 10 ) ) );
	}
}
