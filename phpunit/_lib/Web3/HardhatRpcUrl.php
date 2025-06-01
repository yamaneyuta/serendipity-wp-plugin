<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Service\ChainService;
use Cornix\Serendipity\Core\Constant\ChainID;

/** Hardhatに接続するRPC URLを取得します。 */
class HardhatRpcUrl {
	public function get( int $chain_ID ): string {
		assert( $chain_ID === ChainID::PRIVATENET_L1 || $chain_ID === ChainID::PRIVATENET_L2 );
		$hardhat_rpc_url = ( new ChainService( $chain_ID ) )->rpcURL();
		assert( ! is_null( $hardhat_rpc_url ) );
		return $hardhat_rpc_url;
	}
}
