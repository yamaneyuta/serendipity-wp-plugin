<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Web3;

use Cornix\Serendipity\Core\Service\Factory\ChainServiceFactory;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;

class TokenClientFactory {
	/**
	 * 指定したトークンに接続するオブジェクトを生成します。
	 */
	public function create( ChainID $chain_ID, Address $contract_address ): TokenClient {
		// チェーンに接続するためのRPC URLを取得
		$chain   = ( new ChainServiceFactory() )->create( $GLOBALS['wpdb'] )->getChain( $chain_ID );
		$rpc_url = $chain->rpcURL();
		assert( ! is_null( $rpc_url ), '[2CF9717C] RPC URL is not found. - ' . $chain_ID );

		return new TokenClient( $rpc_url, $contract_address );
	}
}
