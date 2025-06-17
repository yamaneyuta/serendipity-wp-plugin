<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Features\GraphQL;

use Cornix\Serendipity\Core\Features\GraphQL\Resolver\ChainResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\ChainsResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\ConsumerTermsVersionResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\CurrentSellerTermsResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\GetERC20InfoResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\IssueInvoiceResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\NetworkCategoriesResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\NetworkCategoryResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\PostResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\RegisterERC20TokenResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\RequestPaidContentByNonceResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\ResolverBase;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\SalesHistoriesResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\SellerResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\SellingContentResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\SellingPriceResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\ServerSignerResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\SetConfirmationsResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\SetRpcUrlResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\SetSellerAgreedTermsResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\TokenResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\TokensResolver;
use Cornix\Serendipity\Core\Features\GraphQL\Resolver\VerifiableChainsResolver;
use Cornix\Serendipity\Core\Lib\Logger\Logger;
use DI\Container;

class RootValue {

	/**
	 * @param \DI\Container|null $container
	 * @return array<string, mixed>
	 */
	public function get( Container $container ) {

		/** @var array<string,ResolverBase> */
		$resolvers = array(
			// 非公開
			'chain'                     => $container->get( ChainResolver::class ),
			'networkCategory'           => $container->get( NetworkCategoryResolver::class ),
			'sellingContent'            => $container->get( SellingContentResolver::class ),
			'sellingPrice'              => $container->get( SellingPriceResolver::class ),
			'token'                     => $container->get( TokenResolver::class ),

			// Query
			'consumerTermsVersion'      => $container->get( ConsumerTermsVersionResolver::class ),
			'currentSellerTerms'        => $container->get( CurrentSellerTermsResolver::class ),
			'post'                      => $container->get( PostResolver::class ),
			'seller'                    => $container->get( SellerResolver::class ),
			'serverSigner'              => $container->get( ServerSignerResolver::class ),
			'verifiableChains'          => $container->get( VerifiableChainsResolver::class ),

			// Mutation
			'issueInvoice'              => $container->get( IssueInvoiceResolver::class ),
			'requestPaidContentByNonce' => $container->get( RequestPaidContentByNonceResolver::class ),
			'getERC20Info'              => $container->get( GetERC20InfoResolver::class ),
			'registerERC20Token'        => $container->get( RegisterERC20TokenResolver::class ),
			'setSellerAgreedTerms'      => $container->get( SetSellerAgreedTermsResolver::class ),
			'setRpcUrl'                 => $container->get( SetRpcUrlResolver::class ),
			'setConfirmations'          => $container->get( SetConfirmationsResolver::class ),
			// React-Adminの都合によりMutation
			'networkCategories'         => $container->get( NetworkCategoriesResolver::class ),
			'chains'                    => $container->get( ChainsResolver::class ),
			'tokens'                    => $container->get( TokensResolver::class ),
			'salesHistories'            => $container->get( SalesHistoriesResolver::class ),
		);

		$result = array();
		foreach ( $resolvers as $field => $resolver ) {
			$result[ $field ] = function ( array $root_value, array $args ) use ( $resolver ) {
				try {
					return $resolver->resolve( $root_value, $args );
				} catch ( \Throwable $e ) {
					Logger::error( $e );
					throw $e;
				}
			};
		}

		return $result;
	}
}
