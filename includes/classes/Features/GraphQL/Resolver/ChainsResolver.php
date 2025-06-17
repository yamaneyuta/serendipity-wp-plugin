<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Domain\Specification\ChainsFilter;
use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\Infrastructure\Factory\ChainServiceFactory;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;

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

		$filter          = $args['filter'] ?? null;
		$filter_chain_ID = ChainID::fromNullableValue( $filter['chainID'] ?? null );
		/** @var bool|null */
		$filter_is_connectable = $filter['isConnectable'] ?? null;

		// フィルタ処理
		$chains_filter = new ChainsFilter();
		// チェーンIDでフィルタ
		if ( null !== $filter_chain_ID ) {
			$chains_filter = $chains_filter->byChainID( $filter_chain_ID );
		}
		// 接続可能なチェーンでフィルタ
		if ( null !== $filter_is_connectable ) {
			$chains_filter = $chains_filter->byConnectable( $filter_is_connectable );
		}

		// フィルタを適用したチェーン一覧を取得
		$chain_service = ( new ChainServiceFactory() )->create();
		$chains        = $chains_filter->apply( $chain_service->getAllChains() );

		return array_map(
			fn( $chain ) => $root_value['chain'](
				$root_value,
				array(
					'chainID' => $chain->id()->value(),
				)
			),
			$chains
		);
	}
}
