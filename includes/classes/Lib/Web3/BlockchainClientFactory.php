<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Web3;

use Cornix\Serendipity\Core\Lib\Repository\Settings\RpcUrlSetting;

class BlockchainClientFactory {
	/**
	 * 指定したチェーンに接続するオブジェクトを生成します。
	 */
	public function create( int $chain_ID ): BlockchainClient {
		// チェーンに接続するためのRPC URLを取得
		$rpc_url = ( new RpcUrlSetting() )->get( $chain_ID );
		if ( is_null( $rpc_url ) ) {
			throw new \Exception( '[4513DF1F] RPC URL is not found. - ' . $chain_ID );
		}

		return new BlockchainClient( $rpc_url );
	}
}
