<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Service\Factory\TermsServiceFactory;

class SellerResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		// `agreedTerms`以外のプロパティが増えた場合はResolverを分割すること。

		$agreed_terms = null;

		$signed_seller_terms = ( new TermsServiceFactory() )->create()->getSignedSellerTerms();

		if ( null !== $signed_seller_terms ) {
			$agreed_terms = array(
				'version'   => $signed_seller_terms->terms()->version()->value(),
				'message'   => $signed_seller_terms->terms()->message(),
				'signature' => $signed_seller_terms->signature(),
			);
		}

		return array(
			'agreedTerms' => $agreed_terms,
		);
	}
}
