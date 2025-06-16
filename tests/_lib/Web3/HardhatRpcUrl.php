<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Infrastructure\Factory\ChainServiceFactory;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;

/**
 * Hardhatに接続するRPC URLを取得します。
 *
 * @deprecated => Chainクラスから取得するように変更し、このクラスは削除
 */
class HardhatRpcUrl {
	public function get( ChainID $chain_ID ): string {
		assert( $chain_ID->equals( ChainID::privatenet1() ) || $chain_ID->equals( ChainID::privatenet2() ) );
		$chain           = ( new ChainServiceFactory() )->create()->getChain( $chain_ID );
		$hardhat_rpc_url = $chain->rpcURL();
		assert( ! is_null( $hardhat_rpc_url ) );
		return $hardhat_rpc_url;
	}
}
