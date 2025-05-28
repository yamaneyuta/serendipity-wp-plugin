<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Security\Judge;
use Cornix\Serendipity\Core\Repository\ChainData;
use Cornix\Serendipity\Core\Repository\ChainsData;

class ChainsResolver extends ResolverBase {

	/**
	 * チェーン一覧を取得します。
	 *
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		Judge::checkHasAdminRole();  // 管理者権限が必要

		$filter = $args['filter'] ?? null;
		/** @var int|null */
		$filter_chain_ID = $filter['chainID'] ?? null;
		/** @var bool|null */
		$filter_is_connectable = $filter['isConnectable'] ?? null;

		// チェーンID一覧を取得
		$chain_ids = ( new ChainsData() )->chainIDs();

		// チェーンIDでフィルタする場合
		if ( isset( $filter_chain_ID ) ) {
			$chain_ids = array_values(
				array_filter(
					$chain_ids,
					fn( $chain_id ) => $chain_id === $filter_chain_ID
				)
			);
		}

		// 接続可能なチェーンIDでの絞り込みが指定されている場合はRPC URLが登録されいてるもののみ抽出
		if ( isset( $filter_is_connectable ) ) {
			$chain_ids = array_values(
				array_filter(
					$chain_ids,
					fn( $chain_id ) => ( new ChainData( $chain_id ) )->connectable()
				)
			);
		}

		return array_map(
			fn( $chain_id ) => $root_value['chain'](
				$root_value,
				array(
					'chainID' => $chain_id,
				)
			),
			$chain_ids
		);
	}
}
