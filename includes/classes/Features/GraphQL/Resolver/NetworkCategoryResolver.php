<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Algorithm\Filter\ChainsFilter;
use Cornix\Serendipity\Core\Repository\SellableSymbols;
use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\Service\Factory\ChainServiceFactory;
use Cornix\Serendipity\Core\ValueObject\NetworkCategory;

class NetworkCategoryResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		$network_category = NetworkCategory::from( $args['networkCategoryID'] ?? null );

		if ( is_null( $network_category ) ) {
			// ネットワークカテゴリIDの指定は必須
			throw new \InvalidArgumentException( '[FE3B9036] Invalid network category ID.' );
		}

		$sellable_symbols_callback = function () use ( $network_category ) {
			Validate::checkHasEditableRole();  // 投稿編集者権限以上が必要
			return ( new SellableSymbols() )->get( $network_category );
		};

		// ネットワークカテゴリで絞り込んだチェーン一覧を取得
		global $wpdb;
		$chain_service = ( new ChainServiceFactory() )->create( $wpdb );
		$chains_filter = ( new ChainsFilter() )->byNetworkCategory( $network_category );
		$chains        = $chains_filter->apply( $chain_service->getAllChains() );

		$chains_callback = function () use ( $root_value, $chains ) {
			return array_map(
				function ( $chain ) use ( $root_value ) {
					return $root_value['chain']( $root_value, array( 'chainID' => $chain->id() ) );
				},
				$chains
			);
		};

		return array(
			'id'              => $network_category->id(),
			'chains'          => $chains_callback,
			'sellableSymbols' => $sellable_symbols_callback,
		);
	}
}
