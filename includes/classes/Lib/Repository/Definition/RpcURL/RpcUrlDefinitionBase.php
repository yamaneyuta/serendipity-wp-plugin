<?php

namespace Cornix\Serendipity\Core\Lib\Repository\Definition\RpcURL;

abstract class RpcUrlDefinitionBase {
	/**
	 * RPC URLを利用する際の利用規約のURLを取得します。
	 */
	abstract public function termsUrl(): string;

	/**
	 * 指定したチェーンIDに対応するRPC URLを取得します。
	 */
	abstract public function get( int $chain_ID ): ?string;
}
