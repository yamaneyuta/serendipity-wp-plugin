<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Repository\ChainIDs;
use Cornix\Serendipity\Core\Lib\Repository\Constants\ChainID;
use Cornix\Serendipity\Core\Lib\Repository\Environment;

require_once __DIR__ . '/../../../../includes/classes/Lib/Repository/ChainData.php';

class ChainIDsTest extends WP_UnitTestCase {
	/**
	 * 開発モードの場合はプライベートネットのチェーンIDを含むことを確認する。
	 *
	 * @test
	 * @testdox [FC5B2255] ChainIDs::get - Development mode
	 */
	public function testGetDevelopmentMode() {
		// ARRANGE
		$environment_stub = $this->createMock( Environment::class );
		$environment_stub->method( 'isDevelopmentMode' )->willReturn( true );   // 開発モードONの時
		$chainIDs = new ChainIds( $environment_stub );

		// ACT
		$chain_IDs = $chainIDs->get();

		// ASSERT
		$this->assertContains( ChainID::PRIVATENET_L1, $chain_IDs );
	}

	/**
	 * 本番環境の場合はプライベートネットのチェーンIDが含まれないことを確認する。
	 *
	 * @test
	 * @testdox [5D7B24FE] ChainIDs::get - Production mode
	 */
	public function testGetProductionMode() {
		// ARRANGE
		$environment_stub = $this->createMock( Environment::class );
		$environment_stub->method( 'isDevelopmentMode' )->willReturn( false );  // 開発モードOFFの時
		$chainIDs = new ChainIDs( $environment_stub );

		// ACT
		$chain_IDs = $chainIDs->get();

		// ASSERT
		$this->assertNotContains( ChainID::PRIVATENET_L1, $chain_IDs );
	}
}
