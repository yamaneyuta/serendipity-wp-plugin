<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\Service\TermsService;

class SellerResolver extends ResolverBase {


	public function __construct( TermsService $terms_service ) {
		$this->terms_service = $terms_service;
	}
	private TermsService $terms_service;

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		// `agreedTerms`以外のプロパティが増えた場合はResolverを分割すること。

		$agreed_terms = null;

		$signed_seller_terms = $this->terms_service->getSignedSellerTerms();

		if ( null !== $signed_seller_terms ) {
			$agreed_terms = array(
				'version'   => $signed_seller_terms->terms()->version()->value(),
				'message'   => $signed_seller_terms->terms()->message(),
				'signature' => $signed_seller_terms->signature()->value(),
			);
		}

		return array(
			'agreedTerms' => $agreed_terms,
		);
	}
}
