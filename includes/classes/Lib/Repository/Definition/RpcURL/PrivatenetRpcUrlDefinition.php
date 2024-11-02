<?php

namespace Cornix\Serendipity\Core\Lib\Repository\Definition\RpcURL;

use Cornix\Serendipity\Core\Lib\Repository\Constants\ChainID;
use Cornix\Serendipity\Core\Lib\Repository\Environment;

class PrivatenetRpcUrlDefinition extends RpcUrlDefinitionBase {
	/**
	 * RPC URLを利用する際の利用規約のURLを取得します。
	 */
	public function termsUrl(): string {
		// ここは通らないようにする
		throw new \Exception( '[4722A8F0] No terms URL for privatenet' );
	}

	/**
	 * 指定したチェーンIDに対応するRPC URLを取得します。
	 */
	public function get( int $chain_ID ): ?string {

		// プライベートネットのURLを取得する関数
		$privatenet = function ( int $number ): string {
			assert( in_array( $number, array( 1, 2 ) ) );
			$prefix = ( new Environment() )->isDevelopmentMode() ? 'tests-' : '';
			return "http://{$prefix}privatenet-{$number}.local";
		};

		switch ( $chain_ID ) {
			case ChainID::PRIVATENET_L1:
				return $privatenet( 1 );
			case ChainID::PRIVATENET_L2:
				return $privatenet( 2 );
			default:
				return null;
		}
	}
}
