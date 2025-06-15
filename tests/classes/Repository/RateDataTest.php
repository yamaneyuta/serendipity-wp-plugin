<?php
declare(strict_types=1);

require_once 'includes/classes/Repository/RateData.php';

use Cornix\Serendipity\Core\Repository\RateData;
use Cornix\Serendipity\Core\Repository\RateTransient;
use Cornix\Serendipity\Core\Repository\OracleRate;
use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\Service\Factory\ChainServiceFactory;
use Cornix\Serendipity\Core\Domain\ValueObject\Rate;
use Cornix\Serendipity\Core\Domain\ValueObject\SymbolPair;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;

class RateDataTest extends IntegrationTestBase {
	private $rate_data;
	private $rate_transient_mock;
	private $oracle_rate_mock;

	public function setUp(): void {
		parent::setUp();

		$this->rate_transient_mock = $this->createMock( RateTransient::class );
		$this->oracle_rate_mock    = $this->createMock( OracleRate::class );
		$this->rate_data           = new RateData( $this->rate_transient_mock, $this->oracle_rate_mock );
	}

	/**
	 * 一時領域にレート情報が存在する場合、一時領域から取得することを確認
	 *
	 * @test
	 * @testdox [56837E74] RateData::get - get rate from transient
	 */
	public function getRateFromTransient(): void {
		// ARRANGE
		$symbol_pair = new SymbolPair( 'ETH', 'USD' );
		$rate        = new Rate( $symbol_pair, '0x1', 8 );

		$this->rate_transient_mock->expects( $this->once() )->method( 'get' )->with( $symbol_pair )->willReturn( $rate );
		$this->oracle_rate_mock->expects( $this->never() )->method( 'get' );

		// ACT
		$result = $this->rate_data->get( $symbol_pair );

		// ASSERT
		$this->assertSame( $rate, $result );
	}

	/**
	 * 一時領域にレート情報が存在しない場合、Oracleから取得することを確認
	 *
	 * @test
	 * @testdox [808CAD3C] RateData::get - get rate from Oracle
	 */
	public function getRateFromOracle(): void {
		// ARRANGE
		$symbol_pair = new SymbolPair( 'ETH', 'USD' );
		$rate        = new Rate( $symbol_pair, '0x1', 8 );

		$this->rate_transient_mock->expects( $this->once() )->method( 'get' )->with( $symbol_pair )->willReturn( null );
		$this->oracle_rate_mock->expects( $this->once() )->method( 'get' )->with( $symbol_pair )->willReturn( $rate );
		$this->rate_transient_mock->expects( $this->once() )->method( 'set' )->with( $rate );

		// ACT
		$result = $this->rate_data->get( $symbol_pair );

		// ASSERT
		$this->assertSame( $rate, $result );
	}

	/**
	 * 一時領域とOracleからレート情報が取得できない場合、nullを返すことを確認
	 *
	 * @test
	 * @testdox [F04060D8] RateData::get - get rate returns null
	 */
	public function getRateReturnsNull(): void {
		// ARRANGE
		$symbol_pair = new SymbolPair( 'ETH', 'USD' );

		$this->rate_transient_mock->expects( $this->once() )->method( 'get' )->with( $symbol_pair )->willReturn( null );
		$this->oracle_rate_mock->expects( $this->once() )->method( 'get' )->with( $symbol_pair )->willReturn( null );
		$this->rate_transient_mock->expects( $this->never() )->method( 'set' );

		// ACT
		$result = $this->rate_data->get( $symbol_pair );

		// ASSERT
		$this->assertNull( $result );
	}

	/**
	 * 存在しない通貨ペアのレート情報を取得しようとすると、nullを返すことを確認
	 *
	 * @test
	 * @testdox [FFEFD7E7] RateData::get - get rate not exists symbol pair
	 */
	public function getRateNotExistsSymbolPair(): void {
		// ARRANGE
		$symbol_pair = new SymbolPair( 'ETH', 'ETH' );

		// ACT
		$rate = ( new RateData() )->get( $symbol_pair );

		// ASSERT
		$this->assertNull( $rate );
	}

	/**
	 * 2回同じ通貨ペアのレート情報を取得することで、一時領域から取得されることを確認
	 *
	 * @test
	 * @testdox [B3C80F82] RateData::get - get rate twice
	 */
	public function getRateTwice(): void {
		// ARRANGE
		$rate_data   = new RateData( null, $this->oracle_rate_mock );   // OracleRateのみMockを使用
		$symbol_pair = new SymbolPair( 'ETH', 'USD' );
		$rate        = new Rate( $symbol_pair, '0x1', 8 );
		$this->oracle_rate_mock->expects( $this->once() )->method( 'get' )->with( $symbol_pair )->willReturn( $rate );

		// ACT
		$result  = $rate_data->get( $symbol_pair );  // MockのOracleから取得される
		$result2 = $rate_data->get( $symbol_pair ); // 一時領域から取得される

		// ASSERT
		$this->assertNotNull( $result );
		$this->assertEquals( $result->decimals(), 8 );
		$this->assertEquals( $result->decimals(), $result2->decimals() );
		Validate::checkHex( $result->amountHex() );
		$this->assertEquals( $result->amountHex(), $result2->amountHex() );
	}

	/**
	 * 本番環境のOracleからレート情報を取得することを確認
	 *
	 * @test
	 * @testdox [74CAB524] RateData::get - get rate from real Oracle
	 */
	public function getRateFromRealOracle(): void {
		if ( ! ExternalApiAccess::isTesting() ) {
			$this->markTestSkipped( '[96AA6CB4] Skip test.' );
			return;
		}

		// ARRANGE
		$rate_data     = new RateData();
		$symbol_pair   = new SymbolPair( 'ETH', 'USD' );
		$chain_service = ( new ChainServiceFactory() )->create( $GLOBALS['wpdb'] );

		$chain_service->saveRpcURL( ChainID::ethMainnet(), TestRpcUrl::ETH_MAINNET );  // テスト用のRPC URLを設定

		// ACT
		$result = $rate_data->get( $symbol_pair );  // Oracleから取得される

		// ASSERT
		$this->assertNotNull( $result );
		$this->assertEquals( $result->decimals(), 8 );
		Validate::checkHex( $result->amountHex() );
	}
}
