<?php

namespace Cornix\Serendipity\Core\Lib\Repository\Definition\RpcURL;

use Cornix\Serendipity\Core\Lib\Repository\Constants\ChainID;

class PublicNodeRpcUrlDefinition extends RpcUrlDefinitionBase {
	/**
	 * RPC URLを利用する際の利用規約のURLを取得します。
	 */
	public function termsUrl(): string {
		return 'https://www.publicnode.com/terms';
	}

	/**
	 * 指定したチェーンIDに対応するRPC URLを取得します。
	 * Chains list: https://publicnode.com/
	 */
	public function get( int $chain_ID ): ?string {
		switch ( $chain_ID ) {
			// mainnet
			case ChainID::ETH_MAINNET:
				return 'https://ethereum-rpc.publicnode.com';  // https://ethereum.publicnode.com/
			// testnet
			case ChainID::SEPOLIA:
				return 'https://ethereum-sepolia-rpc.publicnode.com'; // https://ethereum.publicnode.com/?sepolia
			default:
				return null;
		}
	}
}
