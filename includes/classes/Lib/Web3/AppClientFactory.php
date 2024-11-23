<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Web3;

use Cornix\Serendipity\Core\Lib\Repository\AppContract;
use Cornix\Serendipity\Core\Lib\Repository\RpcURL;

class AppClientFactory {
	/**
	 * 指定したチェーンのAppコントラクトに接続するオブジェクトを生成します。
	 */
	public function create( int $chain_ID ): AppClient {
		// チェーンに接続するためのRPC URLを取得
		$rpc_url = ( new RpcURL() )->connectableURL( $chain_ID );
		if ( is_null( $rpc_url ) ) {
			throw new \Exception( '[49ACED7A] RPC URL is not found.' );
		}

		// チェーンにデプロイされているAppコントラクトのアドレスを取得
		$contract_address = ( new AppContract() )->address( $chain_ID );
		if ( is_null( $contract_address ) ) {
			throw new \Exception( '[6D37E8B3] Contract address is not found.' );
		}

		return new AppClient( $rpc_url, $contract_address );
	}
}
