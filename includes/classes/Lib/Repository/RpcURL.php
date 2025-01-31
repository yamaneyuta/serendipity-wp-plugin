<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Settings\RpcUrlSetting;

/**
 * チェーンIDに対するRPC URLを取得するクラス
 */
class RpcURL {
	/**
	 * 指定したチェーンのRPC URLを取得します。
	 * ユーザーが設定した値がなければnullを返します。
	 *
	 * @return string|null
	 */
	public function get( int $chain_ID ): ?string {
		return ( new RpcUrlSetting() )->get( $chain_ID );
	}

	/**
	 * 指定したチェーンのRPC URLを設定します。
	 *
	 * @param int         $chain_ID
	 * @param string|null $rpc_url
	 */
	public function set( int $chain_ID, ?string $rpc_url ): void {
		( new RpcUrlSetting() )->set( $chain_ID, $rpc_url );
	}

	/**
	 * 指定したチェーンIDのRPC URLが登録されているかどうかを取得します。
	 *
	 * @param int $chain_ID
	 * @return bool
	 */
	public function isRegistered( int $chain_ID ): bool {
		return ! is_null( $this->get( $chain_ID ) );
	}
}
