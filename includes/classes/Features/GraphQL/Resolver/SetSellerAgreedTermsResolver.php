<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Repository\SellerAgreedTerms;
use Cornix\Serendipity\Core\Lib\Security\Judge;

class SetSellerAgreedTermsResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return bool
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$version = $args['version'];
		/** @var string */
		$signature = $args['signature'];

		// 管理者権限を持っているかどうかをチェック
		Judge::checkHasAdminRole();

		// 販売者の署名情報を保存
		( new SellerAgreedTerms() )->save( $version, $signature );

		return true;
	}
}
