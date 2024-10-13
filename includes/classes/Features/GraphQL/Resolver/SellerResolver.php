<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Repository\SellerAgreedTerms;

class SellerResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		// `agreedTerms`以外のプロパティが増えた場合はResolverを分割すること。

		$agreed_terms = null;

		$seller_agreed_terms = new SellerAgreedTerms();
		if ( $seller_agreed_terms->exists() ) {
			$agreed_terms = array(
				'version'   => $seller_agreed_terms->version(),
				'message'   => $seller_agreed_terms->message(),
				'signature' => $seller_agreed_terms->signature(),
			);
		}

		return array(
			'agreedTerms' => $agreed_terms,
		);
	}
}
