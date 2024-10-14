<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Features\GraphQL;

use Cornix\Serendipity\Core\Features\GraphQL\Resolver\AllNetworkCategoriesResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\ChainResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\CurrentSellerTermsResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\IssueInvoiceResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\NetworkCategoryResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\PostResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\SellerResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\SellingContentResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\SellingNetworkCategoryResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\SellingPriceResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\SetSellerAgreedTermsResolver;

class RootValue {

	/**
	 * @return array<string, mixed>
	 */
	public function get() {

		$resolvers = array(
			// 非公開
			'Chain'                  => new ChainResolver(),
			'NetworkCategory'        => new NetworkCategoryResolver(),
			'SellingContent'         => new SellingContentResolver(),
			'SellingNetworkCategory' => new SellingNetworkCategoryResolver(),
			'SellingPrice'           => new SellingPriceResolver(),

			// Query
			'allNetworkCategories'   => new AllNetworkCategoriesResolver(),
			'currentSellerTerms'     => new CurrentSellerTermsResolver(),
			'post'                   => new PostResolver(),
			'seller'                 => new SellerResolver(),

			// Mutation
			'issueInvoice'           => new IssueInvoiceResolver(),
			'setSellerAgreedTerms'   => new SetSellerAgreedTermsResolver(),
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
