<?php

namespace Cornix\Serendipity\Core\Lib\Repository\Definition\RpcURL;

use Cornix\Serendipity\Core\Lib\Repository\Constants\ChainID;

class AnkrRpcUrlDefinition extends RpcUrlDefinitionBase {
	/**
	 * RPC URLを利用する際の利用規約のURLを取得します。
	 */
	public function termsUrl(): string {
		return 'https://www.ankr.com/terms/';
	}

	/**
	 * 指定したチェーンIDに対応するRPC URLを取得します。
	 * Chains list: https://www.ankr.com/rpc/
	 */
	public function get( int $chain_ID ): ?string {
		switch ( $chain_ID ) {
			// mainnet
			case ChainID::ETH_MAINNET:
				return 'https://rpc.ankr.com/eth';  // https://www.ankr.com/rpc/eth/
			case ChainID::POLYGON_ZK_EVM:
				return 'https://rpc.ankr.com/polygon_zkevm'; // https://www.ankr.com/rpc/polygon_zkevm/
			// testnet
			case ChainID::SEPOLIA:
				return 'https://rpc.ankr.com/eth_sepolia'; // https://www.ankr.com/rpc/eth/
			case ChainID::POLYGON_ZK_EVM_CARDONA:
				return 'https://rpc.ankr.com/polygon_zkevm_cardona'; // https://www.ankr.com/rpc/polygon_zkevm/
			default:
				return null;
		}
	}
}
