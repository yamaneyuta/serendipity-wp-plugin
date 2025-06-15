<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\Application\Factory\TermsServiceFactory;

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
		Validate::checkHasAdminRole();

		//
		// TODO: 引数にアドレスを追加し、署名を検証するロジックを追加
		//

		// 販売者の署名を保存
		( new TermsServiceFactory() )->create()->saveSellerSignature( $signature );

		return true;
	}
}
