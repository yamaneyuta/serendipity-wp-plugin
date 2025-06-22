<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\Service\TermsService;
use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;

class CurrentSellerTermsResolver extends ResolverBase {

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
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {

		$this->user_access_checker->checkHasAdminRole(); // 管理者権限が必要

		// 最新の販売者向け利用規約の情報を取得
		$current_seller_terms = $this->terms_service->getCurrentSellerTerms();
		return array(
			'version' => $current_seller_terms->version()->value(),
			'message' => $current_seller_terms->message()->value(),
		);
	}
}
