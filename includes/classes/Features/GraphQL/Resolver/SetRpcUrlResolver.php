<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Calc\Hex;
use Cornix\Serendipity\Core\Lib\Repository\RpcURL;
use Cornix\Serendipity\Core\Lib\Security\Judge;
use Cornix\Serendipity\Core\Lib\Web3\BlockchainClient;

class SetRpcUrlResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return bool
	 */
	public function resolve( array $root_value, array $args ) {
		// 管理者権限を持っているかどうかをチェック
		Judge::checkHasAdminRole();

		/** @var int */
		$chain_ID = $args['chainID'];
		/** @var string|null */
		$rpc_url = $args['rpcURL'] ?? null;

		if ( ! is_null( $rpc_url ) ) {
			$actual_chain_ID_hex = ( new BlockchainClient( $rpc_url ) )->getChainIDHex();
			if ( Hex::from( $chain_ID ) !== $actual_chain_ID_hex ) {
				throw new \InvalidArgumentException( '[0AD91082] Invalid chain ID. expected: ' . var_export( Hex::from( $chain_ID ), true ) . ', actual: ' . var_export( $actual_chain_ID_hex, true ) );
			}
		}

		// RPC URLを保存
		( new RpcURL() )->set( $chain_ID, $rpc_url );

		return true;
	}
}
