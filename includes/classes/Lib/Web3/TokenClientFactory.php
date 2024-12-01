<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Web3;

use Cornix\Serendipity\Core\Lib\Repository\RpcURL;
use Cornix\Serendipity\Core\Lib\Security\Judge;

class TokenClientFactory {
	/**
	 * 指定したトークンに接続するオブジェクトを生成します。
	 */
	public function create( int $chain_ID, string $contract_address ): TokenClient {
		assert( Judge::isAddress( $contract_address ), '[EAC4D25B] Invalid contract address. ' . $contract_address );

		// チェーンに接続するためのRPC URLを取得
		$rpc_url = ( new RpcURL() )->connectableURL( $chain_ID );
		assert( ! is_null( $rpc_url ), '[2CF9717C] RPC URL is not found.' );

		return new TokenClient( $rpc_url, $contract_address );
	}
}
