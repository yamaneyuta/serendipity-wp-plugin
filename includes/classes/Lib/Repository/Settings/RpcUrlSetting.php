<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository\Settings;

use Cornix\Serendipity\Core\Lib\Repository\Option\OptionFactory;
use Cornix\Serendipity\Core\Lib\Security\Judge;

/**
 * ユーザーが(管理画面で)設定したRPC URLを取得または設定するクラス
 */
class RpcUrlSetting {
	/**
	 * 指定したチェーンに対応する、ユーザーが設定したRPC URLを取得します。
	 *
	 * @param int $chain_ID
	 * @return string|null
	 */
	public function get( int $chain_ID ): ?string {
		$rpc_url = ( new OptionFactory() )->rpcURL( $chain_ID )->get();
		assert( is_null( $rpc_url ) || Judge::isUrl( $rpc_url ) );
		return $rpc_url;
	}

	/**
	 * 指定したチェーンのRPC URLを設定します。
	 * RPC URLを削除する場合はnullを指定します。
	 *
	 * @param int         $chain_ID
	 * @param string|null $rpc_url ユーザーが管理画面で指定したRPC URL(削除する場合はnull)
	 */
	public function set( int $chain_ID, ?string $rpc_url ): void {
		// 引数チェック
		Judge::isChainID( $chain_ID );
		if ( ! is_null( $rpc_url ) && ! Judge::isUrl( $rpc_url ) ) {
			throw new \InvalidArgumentException( '[3AFBCE8C] Invalid RPC URL. - rpc_url: ' . var_export( $rpc_url, true ) );
		}

		$rpc_option = ( new OptionFactory() )->rpcURL( $chain_ID );
		if ( is_null( $rpc_url ) ) {
			$rpc_option->delete();
		} else {
			$rpc_option->update( $rpc_url );
		}
	}
}
