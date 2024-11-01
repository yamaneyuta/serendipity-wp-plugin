<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Definition\BuiltInRpcUrlDefinition;

class RpcUrls {

	/**
	 * 指定したチェーンに接続できるRPC URL一覧を取得します。
	 *
	 * @param int $chain_ID
	 * @return string[]
	 */
	public function get( int $chain_ID ): array {
		//
		// TODO: SettingsディレクトリのRcpUrlSettingsからユーザーが設定したRPC URLが存在する場合はそれを返す
		//

		// 組み込みのRPC URL一覧を返す
		return ( new BuiltInRpcUrlDefinition() )->getRpcUrls( $chain_ID );
	}
}
