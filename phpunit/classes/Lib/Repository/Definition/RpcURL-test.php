<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Calc\Hex;
use Cornix\Serendipity\Core\Lib\Repository\RpcURL;
use Cornix\Serendipity\Core\Lib\Repository\RpcUserSettings;
use Cornix\Serendipity\Core\Lib\Web3\BlockchainClient;
use Cornix\Serendipity\Core\Types\ChainType;
use Cornix\Serendipity\Core\Types\NetworkCategory;

class RpcUrlTest extends IntegrationTestBase {
	/**
	 * 処理のテストではなく、実装に誤りがないかどうかを確認するテスト
	 * 組み込みのRPC URLを使う場合、チェーンIDとRPC URLの対応が正しいかどうかを確認する
	 *
	 * @test
	 * @testdox [C5DD8F84] RpcURL::get (check built-in RPC URL chain ID) - chainID: $chain_ID
	 * @dataProvider checkBuiltInRpcUrlChainIDDataProvider
	 */
	public function checkBuiltInRpcUrlChainID( int $chain_ID ) {
		if ( ! ExternalApiAccess::isTesting() ) {
			$this->markTestSkipped( '[16DB1148] Skip external access test.' );
			return;
		}
		// // ARRANGE
		// RpcUserSettingsのモックを作成
		// 　- 全ての利用規約に同意している
		// 　- プライベートネットの場合はユーザーが設定したRPC URLを使用する
		// 　　(テスト環境の場合、インストール時にプライベートネットのRPC URLがユーザー設定として登録されるため)
		$rpc_user_settings = $this->createMock( RpcUserSettings::class );
		$rpc_user_settings->method( 'isUseCustomRpcUrl' )->willReturnCallback(
			fn( $chain_ID ) => ChainType::from( $chain_ID )->networkCategory() === NetworkCategory::privatenet()
		);
		$rpc_user_settings->method( 'getRpcURL' )->willReturnCallback( fn( $chain_ID ) => ( new RpcURL() )->get( $chain_ID ) );
		$rpc_user_settings->method( 'getIsAgreedTerms' )->willReturn( true );   // 利用規約に同意している
		$sut = new RpcURL( $rpc_user_settings );

		// ACT
		$rpc_url      = $sut->get( $chain_ID );  // チェーンIDに対応するRPC URLを取得
		$chain_id_hex = is_null( $rpc_url ) ? null : ( new BlockchainClient( $rpc_url ) )->getChainIDHex();   // RPC URLに接続し、チェーンIDを取得

		// ASSERT
		if ( is_null( $rpc_url ) ) {
			// RPC URLがプラグインの組み込みとして定義されていない場合。
			$this->markTestSkipped( '[AB707C84] RPC URL is not found. - chain_ID: ' . $chain_ID );
		} else {
			// RPC URLがプラグインの組み込みとして定義されている場合は
			// 定義されているチェーンIDと実際に取得したチェーンIDが一致するかどうかを確認
			$this->assertEquals( Hex::from( $chain_ID ), $chain_id_hex );
		}
	}
	public function checkBuiltInRpcUrlChainIDDataProvider() {
		return array_map( fn( $chain_ID ) => array( $chain_ID ), ( new TestAllChainID() )->get() );
	}
}
