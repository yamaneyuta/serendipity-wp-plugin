<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Features\GraphQL;

use Cornix\Serendipity\Core\Features\GraphQL\Resolver\AddPayableTokensResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\ChainResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\CurrentSellerTermsResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\IssueInvoiceResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\NetworkCategoriesResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\NetworkCategoryResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\PostResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\RemovePayableTokensResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\SellerResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\SellingContentResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\SellingPriceResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\SetSellerAgreedTermsResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\TokenResolver;

class RootValue {

	/**
	 * @return array<string, mixed>
	 */
	public function get() {

		$resolvers = array(
			// 非公開
			'chain'                => new ChainResolver(),
			'networkCategory'      => new NetworkCategoryResolver(),
			'sellingContent'       => new SellingContentResolver(),
			'sellingPrice'         => new SellingPriceResolver(),
			'token'                => new TokenResolver(),

			// Query
			'currentSellerTerms'   => new CurrentSellerTermsResolver(),
			'post'                 => new PostResolver(),
			'seller'               => new SellerResolver(),

			// Mutation
			'addPayableTokens'     => new AddPayableTokensResolver(),
			'issueInvoice'         => new IssueInvoiceResolver(),
			'removePayableTokens'  => new RemovePayableTokensResolver(),
			'setSellerAgreedTerms' => new SetSellerAgreedTermsResolver(),
			// React-Adminの都合によりMutation
			'networkCategories'    => new NetworkCategoriesResolver(),
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
