<?php

namespace Cornix\Serendipity\Core\Lib\Repository\Definition\RpcURL;

use Cornix\Serendipity\Core\Lib\Repository\Constants\ChainID;

class PolygonLabsRpcUrlDefinition extends RpcUrlDefinitionBase {
	/**
	 * RPC URLを利用する際の利用規約のURLを取得します。
	 */
	public function termsUrl(): string {
		return 'https://polygon.technology/terms-of-use';
	}

	/**
	 * 指定したチェーンIDに対応するRPC URLを取得します。
	 * Chains list: https://docs.polygon.technology/zkEVM/get-started/quick-start/#manually-add-network-to-wallet
	 */
	public function get( int $chain_ID ): ?string {
		switch ( $chain_ID ) {
			// mainnet
			case ChainID::POLYGON_ZK_EVM:
				return 'https://zkevm-rpc.com'; // https://docs.polygon.technology/zkEVM/get-started/quick-start/#manually-add-network-to-wallet
			// testnet
			case ChainID::POLYGON_ZK_EVM_CARDONA:
				return 'https://rpc.cardona.zkevm-rpc.com'; // https://docs.polygon.technology/zkEVM/get-started/quick-start/#manually-add-network-to-wallet
			default:
				return null;
		}
	}
}
