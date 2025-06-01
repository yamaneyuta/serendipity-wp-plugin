<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Constant\ChainID;
use Cornix\Serendipity\Core\Lib\Web3\BlockchainClient;

class BlockchainTest extends IntegrationTestBase {

	/**
	 * チェーンIDをRPC URLにアクセスして取得するテスト
	 *
	 * @test
	 * @testdox [463DA15C] RPC::getChainIDHex() - chain_ID: $chain_ID
	 * @dataProvider getChainIDHexDataProvider
	 */
	public function getChainIDHex( int $chain_ID ) {
		// ARRANGE
		$rpc_url = ( new HardhatRpcUrl() )->get( $chain_ID );
		$sut     = new BlockchainClient( $rpc_url );

		// ACT
		$chain_ID_hex = $sut->getChainIDHex();

		// ASSERT
		$this->assertEquals( $chain_ID, hexdec( $chain_ID_hex ) );
	}

	public function getChainIDHexDataProvider() {
		return array(
			array( ChainID::PRIVATENET_L1 ),
			array( ChainID::PRIVATENET_L2 ),
		);
	}

	/**
	 * ブロック番号をRPC URLにアクセスして取得するテスト
	 *
	 * @test
	 * @testdox [20B19A08] RPC::getBlockNumberHex() - chain_ID: $chain_ID
	 * @dataProvider getBlockNumberHexDataProvider
	 */
	public function getBlockNumberHex( int $chain_ID ) {
		// ARRANGE
		$rpc_url = ( new HardhatRpcUrl() )->get( $chain_ID );
		$sut     = new BlockchainClient( $rpc_url );

		// ACT
		$block_number = $sut->getBlockNumber();

		// ASSERT
		$this->assertGreaterThanOrEqual( 0, hexdec( $block_number->hex() ) );
	}

	public function getBlockNumberHexDataProvider() {
		return array(
			array( ChainID::PRIVATENET_L1 ),
			array( ChainID::PRIVATENET_L2 ),
		);
	}

	/**
	 * アカウントの残高をRPC URLにアクセスして取得するテスト
	 *
	 * @test
	 * @testdox [B94DD0E4] RPC::getBalanceHex() - chain_ID: $chain_ID
	 * @dataProvider getBalanceHexDataProvider
	 */
	public function getBalanceHex( int $chain_ID ) {
		// ARRANGE
		$rpc_url         = ( new HardhatRpcUrl() )->get( $chain_ID );
		$hardhat_account = ( new HardhatAccount() )->deployer();    // hardhat デプロイ用アカウント
		$sut             = new BlockchainClient( $rpc_url );

		// ACT
		$balance_hex = $sut->getBalanceHex( $hardhat_account );

		// ASSERT
		// テスト用アカウントは残高が0以上であることを確認
		$this->assertGreaterThanOrEqual( 0, hexdec( $balance_hex ) );
	}

	public function getBalanceHexDataProvider() {
		return array(
			array( ChainID::PRIVATENET_L1 ),
			array( ChainID::PRIVATENET_L2 ),
		);
	}

	/**
	 * ファイナライズされたブロック番号を取得するテスト
	 *
	 * @test
	 * @testdox [DB4609C4] RPC::getFinalizedBlockNumberHex() - chain_ID: $chain_ID
	 * @dataProvider getFinalizedBlockNumberProvider
	 */
	public function getFinalizedBlockNumberHex( int $chain_ID ) {
		// ARRANGE
		$rpc_url = ( new HardhatRpcUrl() )->get( $chain_ID );
		$sut     = new BlockchainClient( $rpc_url );

		// ACT
		$block_number = $sut->getBlockNumber( 'finalized' );

		// ASSERT
		$this->assertGreaterThanOrEqual( 0, hexdec( $block_number->hex() ) );
	}
	public function getFinalizedBlockNumberProvider() {
		return array(
			array( ChainID::PRIVATENET_L1 ),
			array( ChainID::PRIVATENET_L2 ),
		);
	}
}
