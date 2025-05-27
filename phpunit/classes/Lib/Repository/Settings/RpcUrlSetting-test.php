<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Repository\Constants\ChainID;
use Cornix\Serendipity\Core\Lib\Repository\Settings\RpcUrlSetting;
use Cornix\Serendipity\Core\Types\ChainType;
use Cornix\Serendipity\Core\Types\NetworkCategory;

class RpcUrlSettingTest extends IntegrationTestBase {
	/**
	 * インストール後は、RPC URLが登録されていないことを確認
	 * ただし、テスト中はプライベートネットのみ登録済みであること
	 *
	 * @test
	 * @testdox [C5DD8F84] RpcURL::get - chainID: $chain_ID
	 * @dataProvider getDataProvider
	 */
	public function get( int $chain_ID, bool $is_privatenet ) {
		// ARRANGE
		$sut = new RpcUrlSetting();

		// ACT
		$rpc_url = $sut->get( $chain_ID );  // チェーンIDに対応するRPC URLを取得

		// ASSERT
		$this->assertEquals( $is_privatenet, ! is_null( $rpc_url ) );   // プライベートネットの場合はRPC URLが取得できること
	}
	public function getDataProvider() {
		return array_map(
			fn( $chain_ID ) => array(
				$chain_ID,
				( ChainType::from( $chain_ID ) )->networkCategory() === NetworkCategory::privatenet(), // is_registered -> DBに登録済みかどうか。テスト中はプライベートネットのみ登録されている状態となる。
			),
			ChainID::all()
		);
	}

	/**
	 * RPC URLを登録するテスト
	 *
	 * @test
	 * @testdox [A2F2491F] RpcURL::set
	 */
	public function set() {
		// ARRANGE
		$sut      = new RpcUrlSetting();
		$chain_ID = ChainID::ETH_MAINNET;   // 登録するチェーンID(今回はメインネット。何でもよい)
		$url      = 'http://example.com';        // RPC URL(ダミー)
		$this->assertNull( $sut->get( $chain_ID ) );    // 登録前はnullであることを確認

		// ACT
		$sut->set( $chain_ID, $url );       // RPC URLを登録

		// ASSERT
		$this->assertNotNull( $sut->get( $chain_ID ) );         // 登録後はnullでないことを確認
		$this->assertEquals( $url, $sut->get( $chain_ID ) );    // 登録したRPC URLが取得できること
	}
}
