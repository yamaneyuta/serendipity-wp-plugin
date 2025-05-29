<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Web3;

use Cornix\Serendipity\Core\Lib\Security\Judge;
use Cornix\Serendipity\Core\Entity\Chain;

class TokenClientFactory {
	/**
	 * 指定したトークンに接続するオブジェクトを生成します。
	 */
	public function create( int $chain_ID, string $contract_address ): TokenClient {
		assert( Judge::isAddress( $contract_address ), '[EAC4D25B] Invalid contract address. ' . $contract_address );

		// チェーンに接続するためのRPC URLを取得
		$rpc_url = ( new Chain( $chain_ID ) )->rpcURL();
		assert( ! is_null( $rpc_url ), '[2CF9717C] RPC URL is not found. - ' . $chain_ID );

		return new TokenClient( $rpc_url, $contract_address );
	}
}
