<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Repository\RpcUrls;

/** Hardhatに接続するRPC URLを取得します。 */
class HardhatRpcUrl {
	public function get( int $chain_ID ) {
		return ( new RpcUrls() )->get( $chain_ID )[0];
	}
}
