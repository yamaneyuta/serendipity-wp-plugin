<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Test\Presentation\GraphQL\Resolver\CurrentSellerTermsResolver;

use Cornix\Serendipity\TestLib\PHPUnit\UnitTestCaseBase;

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
