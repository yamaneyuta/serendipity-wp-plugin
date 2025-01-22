<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Definition\BuiltInRPC\BuiltInRpcProviderDefinition;
use Cornix\Serendipity\Core\Lib\Repository\Definition\BuiltInRPC\BuiltInRpcUrlDefinition;
use Cornix\Serendipity\Core\Lib\Security\Judge;

class RpcURL {

	public function __construct( RpcUserSettings $rpc_user_settings = null ) {
		$this->rpc_user_settings = $rpc_user_settings ?? new RpcUserSettings();
	}

	private RpcUserSettings $rpc_user_settings;

	/**
	 * 指定したチェーンに接続するためのRPC URLを取得します。
	 *
	 * @param int $chain_ID
	 */
	public function get( int $chain_ID ): ?string {
		if ( $this->rpc_user_settings->isUseCustomRpcUrl( $chain_ID ) ) {
			// ユーザーが設定したRPC URLを使用する場合
			// 設定されたRPC URLを取得して返す
			assert( Judge::isUrl( $this->rpc_user_settings->getRpcURL( $chain_ID ) ) );
			return $this->rpc_user_settings->getRpcURL( $chain_ID );
		} else {
			// ユーザーが設定したRPC URLを使用しない場合
			// 組み込みのRPC URLを返すが、利用規約に同意していない場合はnullを返す

			// 指定されたチェーンで接続するRPCプロバイダを取得
			$rpc_provider = ( new BuiltInRpcProviderDefinition() )->get( $chain_ID );

			if ( $this->rpc_user_settings->getIsAgreedTerms( $rpc_provider ) ) {
				// 利用規約に同意している場合は組み込みのRPC URLを返す
				// ※ nullの場合もあることに注意
				return ( new BuiltInRpcUrlDefinition() )->get( $rpc_provider, $chain_ID );
			} else {
				// 利用規約に同意していない場合はnullを返す
				return null;
			}
		}
	}
}
