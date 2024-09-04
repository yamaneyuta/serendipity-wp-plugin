<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Features\GraphQL;

use Cornix\Serendipity\Core\Features\GraphQL\Resolver\WidgetAttributesResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\SellableSymbolsResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\SellingNetworkResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\SellingPriceResolver;

class RootValue {

	/**
	 * @return array<string, mixed>
	 */
	public function get() {
		global $wpdb;

		$resolvers = array(
			'widgetAttributes' => new WidgetAttributesResolver(),
			// Query
			'sellingPrice'     => new SellingPriceResolver(),
			'sellingNetwork'   => new SellingNetworkResolver(),
			'sellableSymbols'  => new SellableSymbolsResolver(),
			// Mutation
		);

		$result = array();
		foreach ( $resolvers as $field => $resolver ) {
			$result[ $field ] = function ( array $root_value, array $args ) use ( $resolver ) {
				return $resolver->resolve( $root_value, $args );
			};
		}

		return $result;
	}
}
