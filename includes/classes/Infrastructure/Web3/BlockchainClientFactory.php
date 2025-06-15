<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Web3;

use Cornix\Serendipity\Core\Application\Factory\ChainServiceFactory;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;

class BlockchainClientFactory {
	/**
	 * 指定したチェーンに接続するオブジェクトを生成します。
	 */
	public function create( ChainID $chain_ID ): BlockchainClient {
		// チェーンに接続するためのRPC URLを取得
		$chain   = ( new ChainServiceFactory() )->create()->getChain( $chain_ID );
		$rpc_url = $chain->rpcURL();
		if ( is_null( $rpc_url ) ) {
			throw new \Exception( '[4513DF1F] RPC URL is not found. - ' . $chain_ID );
		}

		return new BlockchainClient( $rpc_url );
	}
}
