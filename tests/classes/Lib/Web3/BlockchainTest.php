<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Web3\BlockchainClient;
use Cornix\Serendipity\Core\ValueObject\ChainID;

class BlockchainTest extends IntegrationTestBase {

	/**
	 * チェーンIDをRPC URLにアクセスして取得するテスト
	 *
	 * @test
	 * @testdox [463DA15C] RPC::getChainIDHex() - chain_ID: $chain_ID
	 * @dataProvider getChainIDHexDataProvider
	 */
	public function getChainIDHex( ChainID $chain_ID ) {
		// ARRANGE
		$rpc_url = ( new HardhatRpcUrl() )->get( $chain_ID );
		$sut     = new BlockchainClient( $rpc_url );

		// ACT
		$chain_ID_hex = $sut->getChainIDHex();

		// ASSERT
		$this->assertEquals( $chain_ID->value(), hexdec( $chain_ID_hex ) );
	}

	public function getChainIDHexDataProvider() {
		return array(
			array( ChainID::privatenet1() ),
			array( ChainID::privatenet2() ),
		);
	}

	/**
	 * ブロック番号をRPC URLにアクセスして取得するテスト
	 *
	 * @test
	 * @testdox [20B19A08] RPC::getBlockNumber() - chain_ID: $chain_ID
	 * @dataProvider getBlockNumberHexDataProvider
	 */
	public function getBlockNumberHex( ChainID $chain_ID ) {
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
			array( ChainID::privatenet1() ),
			array( ChainID::privatenet2() ),
		);
	}

	/**
	 * アカウントの残高をRPC URLにアクセスして取得するテスト
	 *
	 * @test
	 * @testdox [B94DD0E4] RPC::getBalanceHex() - chain_ID: $chain_ID
	 * @dataProvider getBalanceHexDataProvider
	 */
	public function getBalanceHex( ChainID $chain_ID ) {
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
			array( ChainID::privatenet1() ),
			array( ChainID::privatenet2() ),
		);
	}

	/**
	 * ファイナライズされたブロック番号を取得するテスト
	 *
	 * @test
	 * @testdox [DB4609C4] RPC::getFinalizedBlockNumberHex() - chain_ID: $chain_ID
	 * @dataProvider getFinalizedBlockNumberProvider
	 */
	public function getFinalizedBlockNumberHex( ChainID $chain_ID ) {
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
			array( ChainID::privatenet1() ),
			array( ChainID::privatenet2() ),
		);
	}
}
