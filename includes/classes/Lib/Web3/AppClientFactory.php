<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Web3;

use Cornix\Serendipity\Core\Lib\Repository\AppContract;
use Cornix\Serendipity\Core\Lib\Repository\Settings\RpcUrlSetting;

class AppClientFactory {
	/**
	 * 指定したチェーンのAppコントラクトに接続するオブジェクトを生成します。
	 */
	public function create( int $chain_ID ): AppClient {
		// チェーンに接続するためのRPC URLを取得
		$rpc_url = ( new RpcUrlSetting() )->get( $chain_ID );
		if ( is_null( $rpc_url ) ) {
			throw new \Exception( '[49ACED7A] RPC URL is not found. - ' . $chain_ID );
		}

		// チェーンにデプロイされているAppコントラクトのアドレスを取得
		$app_contract = ( new AppContract() )->get( $chain_ID );
		if ( is_null( $app_contract ) ) {
			throw new \Exception( '[6D37E8B3] Contract address is not found. - ' . $chain_ID );
		}

		return new AppClient( $rpc_url, $app_contract->address() );
	}
}
