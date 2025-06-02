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

class RootValue {

	/**
	 * @return array<string, mixed>
	 */
	public function get() {

		$resolvers = array(
			// 非公開
			'chain'                     => new ChainResolver(),
			'networkCategory'           => new NetworkCategoryResolver(),
			'sellingContent'            => new SellingContentResolver(),
			'sellingPrice'              => new SellingPriceResolver(),
			'token'                     => new TokenResolver(),

			// Query
			'consumerTermsVersion'      => new ConsumerTermsVersionResolver(),
			'currentSellerTerms'        => new CurrentSellerTermsResolver(),
			'post'                      => new PostResolver(),
			'seller'                    => new SellerResolver(),
			'serverSigner'              => new ServerSignerResolver(),
			'verifiableChains'          => new VerifiableChainsResolver(),

			// Mutation
			'issueInvoice'              => new IssueInvoiceResolver(),
			'requestPaidContentByNonce' => new RequestPaidContentByNonceResolver(),
			'getERC20Info'              => new GetERC20InfoResolver(),
			'registerERC20Token'        => new RegisterERC20TokenResolver(),
			'setSellerAgreedTerms'      => new SetSellerAgreedTermsResolver(),
			'setRpcUrl'                 => new SetRpcUrlResolver(),
			'setConfirmations'          => new SetConfirmationsResolver(),
			// React-Adminの都合によりMutation
			'networkCategories'         => new NetworkCategoriesResolver(),
			'chains'                    => new ChainsResolver(),
			'tokens'                    => new TokensResolver(),
			'salesHistories'            => new SalesHistoriesResolver(),
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
