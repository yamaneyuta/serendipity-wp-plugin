<?php
declare(strict_types=1);

namespace Cornix\Serendipity\TestCase\Presentation\GraphQL\Resolver\SetSellerAgreedTermsResolver;

use Cornix\Serendipity\Test\PHPUnit\UnitTestCaseBase;

class SetSellerAgreedTermsResolverBase extends UnitTestCaseBase {
	/**
	 * GraphQL mutation for setSellerAgreedTerms
	 */
	protected const SET_SELLER_AGREED_TERMS_MUTATION = <<<GRAPHQL
        mutation SetSellerAgreedTerms(
            \$version: Int!,
            \$signature: String!
        ) {
            setSellerAgreedTerms(version: \$version, signature: \$signature)
        }
    GRAPHQL;
}
