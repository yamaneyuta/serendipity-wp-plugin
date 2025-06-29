<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Domain\Service\SellerService;

class SellerResolver extends ResolverBase {

	public function __construct( SellerService $seller_service ) {
		$this->seller_service = $seller_service;
	}
	private SellerService $seller_service;

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		// `agreedTerms`以外のプロパティが増えた場合はResolverを分割すること。

		$agreed_terms = null;

		$signed_seller_terms = $this->seller_service->getSellerSignedTerms();

		if ( null !== $signed_seller_terms ) {
			$agreed_terms = array(
				'version'   => $signed_seller_terms->terms()->version()->value(),
				'message'   => $signed_seller_terms->terms()->message()->value(),
				'signature' => $signed_seller_terms->signature()->value(),
			);
		}

		return array(
			'agreedTerms' => $agreed_terms,
		);
	}
}
