<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Features\GraphQL;

use Cornix\Serendipity\Core\Features\GraphQL\Resolver\IssuePurchaseTicketResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\PostTitleResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\SellableSymbolsResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\SellingNetworkCategoryIdResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\SellingPostContentInfoResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\SellingPriceResolver;

class RootValue {

	/**
	 * @return array<string, mixed>
	 */
	public function get() {

		$resolvers = array(
			// Query
			'postTitle'              => new PostTitleResolver(),
			'sellingPrice'           => new SellingPriceResolver(),
			'sellingPostContentInfo' => new SellingPostContentInfoResolver(),

			'sellingNetworkCategory' => new SellingNetworkCategoryIdResolver(),
			'sellableSymbols'        => new SellableSymbolsResolver(),
			// Mutation
			'issuePurchaseTicket'    => new IssuePurchaseTicketResolver(),
		);

		$result = array();
		foreach ( $resolvers as $field => $resolver ) {
			$result[ $field ] = function ( array $root_value, array $args ) use ( $resolver ) {
				try {
					return $resolver->resolve( $root_value, $args );
				} catch ( \Throwable $e ) {
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
