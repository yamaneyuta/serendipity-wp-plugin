<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Repository\NetworkCategoryDefinition;
use Cornix\Serendipity\Core\Lib\Repository\SellableSymbols;
use Cornix\Serendipity\Core\Lib\Security\Judge;
use Cornix\Serendipity\Core\Types\NetworkCategory;

class NetworkCategoryResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$network_category_id = $args['networkCategoryID'];
		$network_category    = NetworkCategory::from( $network_category_id );

		$sellable_symbols_callback = function () use ( $network_category ) {
			Judge::checkHasEditableRole();  // 投稿編集者権限以上が必要
			return ( new SellableSymbols() )->get( $network_category );
		};

		$chains_callback = function () use ( $root_value, $network_category ) {
			$chain_IDs = ( new NetworkCategoryDefinition() )->getAllChainID( $network_category );
			return array_map(
				function ( $chain_ID ) use ( $root_value ) {
					return $root_value['chain']( $root_value, array( 'chainID' => $chain_ID ) );
				},
				$chain_IDs
			);
		};

		return array(
			'id'              => $network_category_id,
			'chains'          => $chains_callback,
			'sellableSymbols' => $sellable_symbols_callback,
		);
	}
}
