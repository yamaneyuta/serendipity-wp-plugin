<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Features\GraphQL;

use Cornix\Serendipity\Core\Features\GraphQL\Resolver\WidgetAttributesResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\SellableSymbolsResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\SellingNetworkCategoryIdResolver;
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
			'sellingNetwork'   => new SellingNetworkCategoryIdResolver(),
			'sellableSymbols'  => new SellableSymbolsResolver(),
			// Mutation
		);

		$result = array();
		foreach ( $resolvers as $field => $resolver ) {
			$result[ $field ] = function ( array $root_value, array $args ) use ( $resolver ) {
				try {
					return $resolver->resolve( $root_value, $args );
				} catch ( \Exception $e ) {
					if ( 'testing' !== getenv( 'APP_ENV' ) ) {
						// TODO: use logger
						error_log( $e->getMessage() );
						error_log( $e->getTraceAsString() );
					}
					throw $e;
				}
			};
		}

		return $result;
	}
}
