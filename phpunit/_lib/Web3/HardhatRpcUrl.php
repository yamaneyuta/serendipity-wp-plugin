<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Repository\RpcURL;

/** Hardhatに接続するRPC URLを取得します。 */
class HardhatRpcUrl {
	public function get( int $chain_ID ): string {
		return ( new RpcURL() )->connectableURL( $chain_ID );
	}
}
