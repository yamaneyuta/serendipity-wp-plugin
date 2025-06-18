<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\Service\TermsService;
use Cornix\Serendipity\Core\Domain\ValueObject\Signature;
use Cornix\Serendipity\Core\Lib\Security\Validate;

class SetSellerAgreedTermsResolver extends ResolverBase {

	public function __construct( TermsService $terms_service ) {
		$this->terms_service = $terms_service;
	}

	private TermsService $terms_service;

	/**
	 * #[\Override]
	 *
	 * @return bool
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$version   = $args['version'];
		$signature = new Signature( $args['signature'] );

		// 管理者権限を持っているかどうかをチェック
		Validate::checkHasAdminRole();

		//
		// TODO: 引数にアドレスを追加し、署名を検証するロジックを追加
		//

		// 販売者の署名を保存
		$this->terms_service->saveSellerSignature( $signature );

		return true;
	}
}
