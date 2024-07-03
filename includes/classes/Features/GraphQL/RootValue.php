<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Features\GraphQL;

use Cornix\Serendipity\Core\Features\GraphQL\Resolver\PostSettingResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\SellableSymbolsResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\SellingPriceResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\SetPostSettingResolver;

class RootValue {

	/**
	 * @return array<string, mixed>
	 */
	public function get() {
		global $wpdb;

		$resolvers = array(
			'postSetting'     => new PostSettingResolver( $wpdb ),
			// Query
			'sellingPrice'    => new SellingPriceResolver(),
			'sellableSymbols' => new SellableSymbolsResolver(),
			// Mutation
			'setPostSetting'  => new SetPostSettingResolver( $wpdb ),
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
