<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Repository\Constants\ChainID;
use Cornix\Serendipity\Core\Lib\Repository\AppContract;
use Cornix\Serendipity\Core\Lib\Repository\Environment;

class AppContractTest extends IntegrationTestBase {

	private function createEnvironmentStub( $is_development_mode ) {
		$environment_stub = $this->createMock( Environment::class );
		$environment_stub->method( 'isDevelopmentMode' )->willReturn( $is_development_mode );

		return $environment_stub;
	}

	/**
	 * 開発モードがONの場合、プライベートネットワークのAppコントラクト情報が取得できることを確認
	 *
	 * @test
	 * @testdox [4F71C839] AppContract::address() - is development mode
	 */
	public function get_isDevelopmentMode() {
		// ARRANGE
		$environment_stub = $this->createEnvironmentStub( true ); // 開発モードON
		$sut              = new AppContract( $environment_stub );

		// ACT
		$ret1 = $sut->get( ChainID::PRIVATENET_L1 );
		$ret2 = $sut->get( ChainID::PRIVATENET_L2 );

		// ASSERT
		// 開発モードがONの場合は、Appコントラクトのアドレスが取得できる
		$this->assertEquals( ChainID::PRIVATENET_L1, $ret1->chainID() );
		$this->assertMatchesRegularExpression( '/^0x[0-9a-fA-F]{40}$/', $ret1->address() );
		$this->assertEquals( ChainID::PRIVATENET_L2, $ret2->chainID() );
		$this->assertMatchesRegularExpression( '/^0x[0-9a-fA-F]{40}$/', $ret2->address() );
	}

	/**
	 * 開発モードがOFFの場合、プライベートネットワークのAppコントラクトアドレスが取得できないことを確認
	 *
	 * @test
	 * @testdox [A966F033] AppContract::address() - is not development mode
	 */
	public function address_isNotDevelopmentMode() {
		// ARRANGE
		$environment_stub = $this->createEnvironmentStub( false ); // 開発モードOFF
		$sut              = new AppContract( $environment_stub );

		// ACT
		$ret1 = $sut->get( ChainID::PRIVATENET_L1 );
		$ret2 = $sut->get( ChainID::PRIVATENET_L2 );

		// ASSERT
		// 開発モードがOFFの場合は、Appコントラクトのアドレスが取得できない
		$this->assertNull( $ret1 );
		$this->assertNull( $ret2 );
	}
}
