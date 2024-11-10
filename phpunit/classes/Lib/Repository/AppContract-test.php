<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Repository\Constants\ChainID;
use Cornix\Serendipity\Core\Lib\Repository\AppContract;
use Cornix\Serendipity\Core\Lib\Repository\Environment;

class AppContractTest extends WP_UnitTestCase {

	private function createEnvironmentStub( $is_development_mode ) {
		$environment_stub = $this->createMock( Environment::class );
		$environment_stub->method( 'isDevelopmentMode' )->willReturn( $is_development_mode );

		return $environment_stub;
	}

	/**
	 * 開発モードがONの場合、プライベートネットワークのチェーンIDが含まれることを確認
	 *
	 * @test
	 * @testdox [E9D43FA1] AppContract::allChainIDs() - is development mode
	 */
	public function allChainIDs_isDevelopmentMode() {
		// ARRANGE
		$environment_stub = $this->createEnvironmentStub( true ); // 開発モードON
		$sut              = new AppContract( $environment_stub );

		// ACT
		$ret = $sut->allChainIDs();

		// ASSERT
		// 開発モードがONの場合は、プライベートネットワークのチェーンIDも含まれる
		$this->assertTrue( in_array( ChainID::PRIVATENET_L1, $ret ) );
		$this->assertTrue( in_array( ChainID::PRIVATENET_L2, $ret ) );
	}

	/**
	 * 開発モードがOFFの場合、プライベートネットワークのチェーンIDが含まれないことを確認
	 *
	 * @test
	 * @testdox [8443AA9F] AppContract::allChainIDs() - is not development mode
	 */
	public function allChainIDs_isNotDevelopmentMode() {
		// ARRANGE
		$environment_stub = $this->createEnvironmentStub( false );  // 開発モードOFF
		$sut              = new AppContract( $environment_stub );

		// ACT
		$ret = $sut->allChainIDs();

		// ASSERT
		// 開発モードがOFFの場合は、プライベートネットワークのチェーンIDは含まれない
		$this->assertFalse( in_array( ChainID::PRIVATENET_L1, $ret ) );
		$this->assertFalse( in_array( ChainID::PRIVATENET_L2, $ret ) );
	}

	/**
	 * 開発モードがONの場合、プライベートネットワークのAppコントラクトアドレスが取得できることを確認
	 *
	 * @test
	 * @testdox [4F71C839] AppContract::address() - is development mode
	 */
	public function address_isDevelopmentMode() {
		// ARRANGE
		$environment_stub = $this->createEnvironmentStub( true ); // 開発モードON
		$sut              = new AppContract( $environment_stub );

		// ACT
		$ret1 = $sut->address( ChainID::PRIVATENET_L1 );
		$ret2 = $sut->address( ChainID::PRIVATENET_L2 );

		// ASSERT
		// 開発モードがONの場合は、Appコントラクトのアドレスが取得できる
		$this->assertIsString( $ret1 );
		$this->assertRegExp( '/^0x[0-9a-fA-F]{40}$/', $ret1 );
		$this->assertIsString( $ret2 );
		$this->assertRegExp( '/^0x[0-9a-fA-F]{40}$/', $ret2 );
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
		$ret1 = $sut->address( ChainID::PRIVATENET_L1 );
		$ret2 = $sut->address( ChainID::PRIVATENET_L2 );

		// ASSERT
		// 開発モードがOFFの場合は、Appコントラクトのアドレスが取得できない
		$this->assertNull( $ret1 );
		$this->assertNull( $ret2 );
	}
}
