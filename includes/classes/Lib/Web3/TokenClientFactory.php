<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Web3;

use Cornix\Serendipity\Core\Service\ChainService;
use Cornix\Serendipity\Core\ValueObject\Address;

class TokenClientFactory {
	/**
	 * 指定したトークンに接続するオブジェクトを生成します。
	 */
	public function create( int $chain_ID, Address $contract_address ): TokenClient {
		// チェーンに接続するためのRPC URLを取得
		$rpc_url = ( new ChainService( $chain_ID ) )->rpcURL();
		assert( ! is_null( $rpc_url ), '[2CF9717C] RPC URL is not found. - ' . $chain_ID );

		return new TokenClient( $rpc_url, $contract_address );
	}
}
