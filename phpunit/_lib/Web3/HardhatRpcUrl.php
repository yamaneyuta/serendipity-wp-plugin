<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\Repository\BuiltInRpcUrlData;

/** Hardhatに接続するRPC URLを取得します。 */
class HardhatRpcUrl {
	public function get( int $chain_ID ) {
		return ( new BuiltInRpcUrlData() )->getRpcUrls( $chain_ID )[0];
	}
}
