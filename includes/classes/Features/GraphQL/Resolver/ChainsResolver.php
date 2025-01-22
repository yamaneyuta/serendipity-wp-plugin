<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Repository\ChainData;
use Cornix\Serendipity\Core\Lib\Security\Judge;

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

		// チェーン一覧を取得
		$chains = ( new ChainData() )->all();

		// チェーンIDでフィルタする場合
		if ( isset( $filter_chain_ID ) ) {
			$chains = array_values(
				array_filter(
					$chains,
					fn( $chain ) => $chain->id() === $filter_chain_ID
				)
			);
		}

		// 接続可能なチェーンIDでの絞り込みが指定されている場合はチェーンID一覧をフィルタリング
		if ( isset( $filter_is_connectable ) ) {
			$chains = array_values(
				array_filter(
					$chains,
					fn( $chain ) => $chain->isConnectable() === $filter_is_connectable
				)
			);
		}

		return array_map(
			fn( $chain ) => $root_value['chain'](
				$root_value,
				array(
					'chainID' => $chain->id(),
				)
			),
			$chains
		);
	}
}
