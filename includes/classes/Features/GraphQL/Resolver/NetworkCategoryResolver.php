<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Repository\SellableSymbols;
use Cornix\Serendipity\Core\Lib\Security\Judge;
use Cornix\Serendipity\Core\Service\ChainsService;
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
			Judge::checkHasEditableRole();  // 投稿編集者権限以上が必要
			return ( new SellableSymbols() )->get( $network_category );
		};

		$chains_callback = function () use ( $root_value, $network_category ) {
			return array_map(
				function ( $chain_ID ) use ( $root_value ) {
					return $root_value['chain']( $root_value, array( 'chainID' => $chain_ID ) );
				},
				( new ChainsService() )->chainIDs( $network_category )
			);
		};

		return array(
			'id'              => $network_category->id(),
			'chains'          => $chains_callback,
			'sellableSymbols' => $sellable_symbols_callback,
		);
	}
}
