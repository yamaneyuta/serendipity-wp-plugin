<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Calc\Hex;
use Cornix\Serendipity\Core\Lib\Repository\BuiltInRpcUrlData;
use Cornix\Serendipity\Core\Lib\Repository\PluginInfo;
use Cornix\Serendipity\Core\Lib\Web3\Blockchain;

class BuiltInRpcUrlDataTest extends WP_UnitTestCase {
	/**
	 * 処理のテストではなく、実装漏れの確認を行うためのテスト
	 * BuiltInRpcUrlDataからRPC URLが取得できることを確認
	 *
	 * @test
	 * @testdox [0BACB063] BuiltInRpcUrlData::getRpcUrls exists - chain_id: $chain_id
	 * @dataProvider allChainIdProvider
	 */
	public function getRpcUrlsExists( int $chain_id ) {
		// ARRANGE
		// Do nothing.

		// ACT
		$built_in_rpc_urls = ( new BuiltInRpcUrlData() )->getRpcUrls( $chain_id );

		// ASSERT
		$this->assertGreaterThan( 0, count( $built_in_rpc_urls ) );
	}
	public function allChainIdProvider() {
		return array_map(
			fn( $chain_id ) => array( $chain_id ),
			( new TestAllChainID() )->get()
		);
	}

	/**
	 * ビルトインのRPC URLが使用可能かどうかをテスト
	 *
	 * @test
	 * @testdox [33B40E51] BuiltInRpcUrlData::getRpcUrls connectable - chain_id: $chain_id, rpc_url: $rpc_url
	 * @dataProvider allRpcUrlProvider
	 */
	public function getRpcUrlsConnectable( int $chain_id, string $rpc_url ) {
		if ( ! $this->isTestStatus() ) {
			$this->markTestSkipped( '[E8C72D14] Tests are performed only when they match the minimum version of PHP required. current version: ' . phpversion() );
			return;
		}

		// ARRANGE
		// Do nothing.

		// ACT
		// 最大3回リトライ
		for ( $i = 0; $i < 3; $i++ ) {
			try {
				$chain_id_hex = ( new Blockchain( $rpc_url ) )->getChainIDHex();
				break;
			} catch ( \Throwable $e ) {
				error_log( '[6C1FAB2D] Failed to connect to RPC URL: ' . $rpc_url . ' - ' . $e->getMessage() );
				sleep( 1 );
			}
		}

		// ASSERT
		$this->assertEquals( Hex::from( $chain_id ), $chain_id_hex );
	}
	public function allRpcUrlProvider() {
		$all_chain_ids = ( new TestAllChainID() )->get();
		$provider_data = array();
		foreach ( $all_chain_ids as $chain_id ) {
			$rpc_urls = ( new BuiltInRpcUrlData() )->getRpcUrls( $chain_id );
			foreach ( $rpc_urls as $rpc_url ) {
				$provider_data[] = array( $chain_id, $rpc_url );
			}
		}
		return $provider_data;
	}

	/**
	 * テストを実施する状態かどうかを判定します。
	 * ※ CIで外部アクセスのテスト回数を制限するために使用
	 */
	private function isTestStatus(): bool {
		$is_test_php_version = $this->versionEqual( phpversion(), ( new PluginInfo() )->requiresPHP() );
		$is_test_wp_version  = $this->versionEqual( $GLOBALS['wp_version'], ( new PluginInfo() )->requiresWP() );

		return $is_test_php_version && $is_test_wp_version;
	}
	private function versionEqual( string $version1, string $version2 ): bool {
		$major1 = (int) explode( '.', $version1 )[0];
		$minor1 = (int) explode( '.', $version1 )[1];

		$major2 = (int) explode( '.', $version2 )[0];
		$minor2 = (int) explode( '.', $version2 )[1];

		return $major1 == $major2 && $minor1 == $minor2;
	}
}
