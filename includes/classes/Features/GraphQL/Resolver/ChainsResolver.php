<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Domain\Specification\ChainsFilter;
use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\Service\Factory\ChainServiceFactory;

class ChainsResolver extends ResolverBase {

	/**
	 * チェーン一覧を取得します。
	 *
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		Validate::checkHasAdminRole();  // 管理者権限が必要

		$filter = $args['filter'] ?? null;
		/** @var int|null */
		$filter_chain_ID = $filter['chainID'] ?? null;
		/** @var bool|null */
		$filter_is_connectable = $filter['isConnectable'] ?? null;

		// フィルタ処理
		$chains_filter = new ChainsFilter();
		// チェーンIDでフィルタ
		$chains_filter = isset( $filter_chain_ID ) ? $chains_filter->byChainID( $filter_chain_ID ) : $chains_filter;
		// 接続可能なチェーンでフィルタ
		$chains_filter = isset( $filter_is_connectable ) ? $chains_filter->byConnectable() : $chains_filter;

		// フィルタを適用したチェーン一覧を取得
		global $wpdb;
		$chain_service = ( new ChainServiceFactory() )->create( $wpdb );
		$chains        = $chain_service->getAllChains();
		$chains        = $chains_filter->apply( $chains );

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
