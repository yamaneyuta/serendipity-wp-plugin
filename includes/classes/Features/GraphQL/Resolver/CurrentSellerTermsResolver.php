<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Repository\SellerTerms;
use Cornix\Serendipity\Core\Lib\Security\Validate;

class CurrentSellerTermsResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {

		Validate::checkHasAdminRole(); // 管理者権限が必要

		// 最新の販売者向け利用規約の情報を取得
		$seller_terms = new SellerTerms();
		return array(
			'version' => $seller_terms->currentVersion(),
			'message' => $seller_terms->message( $seller_terms->currentVersion() ),
		);
	}
}
