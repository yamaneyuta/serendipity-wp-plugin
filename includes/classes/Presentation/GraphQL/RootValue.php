<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Presentation\GraphQL;

use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\ChainResolver;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\ChainsResolver;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\ConsumerTermsVersionResolver;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\CurrentSellerTermsResolver;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\GetERC20InfoResolver;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\IssueInvoiceResolver;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\NetworkCategoriesResolver;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\NetworkCategoryResolver;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\PostResolver;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\RegisterERC20TokenResolver;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\RequestPaidContentByNonceResolver;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\ResolverBase;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\SalesHistoriesResolver;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\SellerResolver;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\SellingContentResolver;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\SellingPriceResolver;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\ServerSignerResolver;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\SetConfirmationsResolver;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\SetRpcUrlResolver;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\SetSellerAgreedTermsResolver;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\TokenResolver;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\TokensResolver;
use Cornix\Serendipity\Core\Presentation\GraphQL\Resolver\VerifiableChainsResolver;
use Cornix\Serendipity\Core\Lib\Logger\DeprecatedLogger;
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
					DeprecatedLogger::error( $e );
					throw $e;
				}
			};
		}

		return $result;
	}
}
