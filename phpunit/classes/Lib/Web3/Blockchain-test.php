<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Repository\Constants\ChainID;
use Cornix\Serendipity\Core\Lib\Web3\BlockchainClient;

class BlockchainTest extends IntegrationTestBase {

	/**
	 * チェーンIDをRPC URLにアクセスして取得するテスト
	 *
	 * @test
	 * @testdox [463DA15C] RPC::getChainIDHex() - rpc_url: $rpc_url -> $expected
	 * @dataProvider getChainIDHexDataProvider
	 */
	public function getChainIDHex( string $rpc_url, int $expected ) {
		$sut = new BlockchainClient( $rpc_url );

		$chain_ID = hexdec( $sut->getChainIDHex() );

		$this->assertEquals( $expected, $chain_ID );
	}

	public function getChainIDHexDataProvider() {
		return array(
			array( ( new HardhatRpcUrl() )->get( ChainID::PRIVATENET_L1 ), ChainID::PRIVATENET_L1 ),
			array( ( new HardhatRpcUrl() )->get( ChainID::PRIVATENET_L2 ), ChainID::PRIVATENET_L2 ),
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
		$sut = new BlockchainClient( $rpc_url );

		$block_number_hex = $sut->getBlockNumberHex();

		$this->assertGreaterThanOrEqual( 0, hexdec( $block_number_hex ) );
	}

	public function getBlockNumberHexDataProvider() {
		return array(
			array( ( new HardhatRpcUrl() )->get( ChainID::PRIVATENET_L1 ), ChainID::PRIVATENET_L1 ),
			array( ( new HardhatRpcUrl() )->get( ChainID::PRIVATENET_L2 ), ChainID::PRIVATENET_L2 ),
		);
	}

	/**
	 * アカウントの残高をRPC URLにアクセスして取得するテスト
	 *
	 * @test
	 * @testdox [B94DD0E4] RPC::getBalanceHex() - rpc_url: $rpc_url
	 * @dataProvider getBalanceHexDataProvider
	 */
	public function getBalanceHex( string $rpc_url ) {
		$sut             = new BlockchainClient( $rpc_url );
		$hardhat_account = ( new HardhatAccount() )->deployer();    // hardhat デプロイ用アカウント

		$balance_hex = $sut->getBalanceHex( $hardhat_account );

		// テスト用アカウントは残高が0以上であることを確認
		$this->assertGreaterThanOrEqual( 0, hexdec( $balance_hex ) );
	}

	public function getBalanceHexDataProvider() {
		return array(
			array( ( new HardhatRpcUrl() )->get( ChainID::PRIVATENET_L1 ) ),
			array( ( new HardhatRpcUrl() )->get( ChainID::PRIVATENET_L2 ) ),
		);
	}

	/**
	 * ファイナライズされたブロック番号を取得するテスト
	 *
	 * @test
	 * @testdox [DB4609C4] RPC::getFinalizedBlockNumberHex() - rpc_url: $rpc_url
	 * @dataProvider getFinalizedBlockNumberProvider
	 */
	public function getFinalizedBlockNumberHex( string $rpc_url ) {
		$sut = new BlockchainClient( $rpc_url );

		$block_number_hex = $sut->getBlockNumberHex( 'finalized' );

		$this->assertGreaterThanOrEqual( 0, hexdec( $block_number_hex ) );
	}
	public function getFinalizedBlockNumberProvider() {
		return array(
			array( ( new HardhatRpcUrl() )->get( ChainID::PRIVATENET_L1 ) ),
			array( ( new HardhatRpcUrl() )->get( ChainID::PRIVATENET_L2 ) ),
		);
	}
}
