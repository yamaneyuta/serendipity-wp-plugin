<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Calc\Hex;
use Cornix\Serendipity\Core\Lib\Security\Judge;
use Cornix\Serendipity\Core\Lib\Web3\BlockchainClient;
use Cornix\Serendipity\Core\Service\ChainService;

class SetRpcUrlResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return bool
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$chain_ID = $args['chainID'];
		/** @var string|null */
		$rpc_url = $args['rpcURL'] ?? null;

		Judge::checkHasAdminRole(); // 管理者権限を持っているかどうかをチェック
		Judge::checkChainID( $chain_ID );
		( ! is_null( $rpc_url ) ) && Judge::checkURL( $rpc_url );

		// RPC URLを登録する場合は実際にアクセスしてチェーンIDを取得し、
		// 引数のチェーンIDと一致していることを確認する
		if ( ! is_null( $rpc_url ) ) {
			$actual_chain_ID_hex = ( new BlockchainClient( $rpc_url ) )->getChainIDHex();
			if ( Hex::from( $chain_ID ) !== $actual_chain_ID_hex ) {
				throw new \InvalidArgumentException( '[0AD91082] Invalid chain ID. expected: ' . var_export( Hex::from( $chain_ID ), true ) . ', actual: ' . var_export( $actual_chain_ID_hex, true ) );
			}
		}

		// RPC URLを保存
		try {
			global $wpdb;
			$wpdb->query( 'START TRANSACTION' );
			if ( is_null( $rpc_url ) ) {
				( new ChainService( $chain_ID, $wpdb ) )->deleteRpcURL();
			} else {
				( new ChainService( $chain_ID, $wpdb ) )->setRpcURL( $rpc_url );
			}
			$wpdb->query( 'COMMIT' );
		} catch ( \Throwable $e ) {
			$wpdb->query( 'ROLLBACK' );
			throw $e;
		}

		return true;
	}
}
