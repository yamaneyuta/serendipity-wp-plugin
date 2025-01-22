<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Repository\Constants\ChainID;
use Cornix\Serendipity\Core\Lib\Repository\RpcURL;

/** Hardhatに接続するRPC URLを取得します。 */
class HardhatRpcUrl {
	public function get( int $chain_ID ): string {
		assert( $chain_ID === ChainID::PRIVATENET_L1 || $chain_ID === ChainID::PRIVATENET_L2 );
		return ( new RpcURL() )->get( $chain_ID );
	}
}
