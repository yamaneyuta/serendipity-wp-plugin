<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Constant\ChainID;
use Cornix\Serendipity\Core\Service\Factory\ChainServiceFactory;

/**
 * Hardhatに接続するRPC URLを取得します。
 *
 * @deprecated => Chainクラスから取得するように変更し、このクラスは削除
 */
class HardhatRpcUrl {
	public function get( int $chain_ID ): string {
		assert( $chain_ID === ChainID::PRIVATENET_L1 || $chain_ID === ChainID::PRIVATENET_L2 );
		$chain           = ( new ChainServiceFactory() )->create( $GLOBALS['wpdb'] )->getChain( $chain_ID );
		$hardhat_rpc_url = $chain->rpcURL();
		assert( ! is_null( $hardhat_rpc_url ) );
		return $hardhat_rpc_url;
	}
}
