<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Web3\RPC;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../_lib/Web3/TestRPCUrl.php';

class RPCTest extends TestCase {

	/**
	 * チェーンIDをRPC URLにアクセスして取得するテスト
	 *
	 * @test
	 * @testdox [463DA15C] RPC::getChainIDHex() - rpc_url: $rpc_url -> $expected
	 * @dataProvider getChainIDHexDataProvider
	 */
	public function getChainIDHex( string $rpc_url, string $expected ) {
		$sut = new RPC( $rpc_url );

		$chain_ID_hex = $sut->getChainIDHex();

		$this->assertEquals( $expected, $chain_ID_hex );
	}

	public function getChainIDHexDataProvider() {
		return array(
			array( ( new TestRPCUrl() )->privatenetL1(), '0x7a69' ),    // 31337
			array( ( new TestRPCUrl() )->privatenetL2(), '0x7a6a' ),    // 31338
		);
	}

	/**
	 * ブロック番号をRPC URLにアクセスして取得するテスト
	 *
	 * @test
	 * @testdox [20B19A08] RPC::getBlockNumberHex() - rpc_url: $rpc_url
	 * @dataProvider getBlockNumberHexDataProvider
	 */
	public function getBlockNumberHex( string $rpc_url ) {
		$sut = new RPC( $rpc_url );

		$block_number_hex = $sut->getBlockNumberHex();

		$this->assertGreaterThanOrEqual( 0, hexdec( $block_number_hex ) );
	}

	public function getBlockNumberHexDataProvider() {
		return array(
			array( ( new TestRPCUrl() )->privatenetL1() ),
			array( ( new TestRPCUrl() )->privatenetL2() ),
		);
	}
}
