<?php

namespace Cornix\Serendipity\Core\Lib\Repository\Definition\RpcURL;

use Cornix\Serendipity\Core\Lib\Repository\Constants\ChainID;

class SoneiumRpcUrlDefinition extends RpcUrlDefinitionBase {
	/**
	 * RPC URLを利用する際の利用規約のURLを取得します。
	 */
	public function termsUrl(): string {
		return 'https://docs.soneium.org/docs/tos/';
	}

	/**
	 * 指定したチェーンIDに対応するRPC URLを取得します。
	 * Chains list: https://eth.public-rpc.com/
	 */
	public function get( int $chain_ID ): ?string {
		switch ( $chain_ID ) {
			// testnet
			case ChainID::SONEIUM_MINATO:
				return 'https://rpc.minato.soneium.org';  // https://docs.soneium.org/docs/builders/overview
			default:
				return null;
		}
	}
}
