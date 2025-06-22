<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\Service\TermsService;
use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Domain\ValueObject\Signature;

class SetSellerAgreedTermsResolver extends ResolverBase {

	public function __construct(
		TermsService $terms_service,
		UserAccessChecker $user_access_checker
	) {
		$this->terms_service       = $terms_service;
		$this->user_access_checker = $user_access_checker;
	}

	private TermsService $terms_service;
	private UserAccessChecker $user_access_checker;

	/**
	 * #[\Override]
	 *
	 * @return bool
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$version   = $args['version'];
		$signature = new Signature( $args['signature'] );

		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		//
		// TODO: 引数にアドレスを追加し、署名を検証するロジックを追加
		//

		// 販売者の署名を保存
		$this->terms_service->saveSellerSignature( $signature );

		return true;
	}
}
