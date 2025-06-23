<?php
declare(strict_types=1);

namespace Cornix\Serendipity\TestCase\Presentation\GraphQL\Resolver\CurrentSellerTermsResolver;

use Cornix\Serendipity\Test\PHPUnit\UnitTestCaseBase;

class CurrentSellerTermsResolverBase extends UnitTestCaseBase {
	protected const CURRENT_SELLER_TERMS_QUERY = <<<GRAPHQL
		query CurrentSellerTerms {
			currentSellerTerms {
				version
				message
			}
		}
	GRAPHQL;
}
