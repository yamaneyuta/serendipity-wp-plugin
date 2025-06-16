<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\Infrastructure\Factory\TermsServiceFactory;

class CurrentSellerTermsResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {

		Validate::checkHasAdminRole(); // 管理者権限が必要

		// 最新の販売者向け利用規約の情報を取得
		$current_seller_terms = ( new TermsServiceFactory() )->create()->getCurrentSellerTerms();
		return array(
			'version' => $current_seller_terms->version()->value(),
			'message' => $current_seller_terms->message(),
		);
	}
}
