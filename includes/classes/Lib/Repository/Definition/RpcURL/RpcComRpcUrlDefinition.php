<?php

namespace Cornix\Serendipity\Core\Lib\Repository\Definition\RpcURL;

use Cornix\Serendipity\Core\Lib\Repository\Constants\ChainID;

class RpcComRpcUrlDefinition extends RpcUrlDefinitionBase {
	/**
	 * RPC URLを利用する際の利用規約のURLを取得します。
	 */
	public function termsUrl(): string {
		return 'https://www.ankr.com/terms/'; // 実態はAnkr
	}

	/**
	 * 指定したチェーンIDに対応するRPC URLを取得します。
	 * Chains list: https://eth.public-rpc.com/
	 */
	public function get( int $chain_ID ): ?string {
		switch ( $chain_ID ) {
			// mainnet
			case ChainID::ETH_MAINNET:
				return 'https://eth.public-rpc.com';  // https://eth.public-rpc.com/
			case ChainID::POLYGON_ZK_EVM:
				return 'https://polygon-rpc.com/zkevm'; // https://polygon-rpc.com/zkevm
			default:
				return null;
		}
	}
}
