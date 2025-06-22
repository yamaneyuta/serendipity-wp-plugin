<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\Service\UserAccessChecker;
use Cornix\Serendipity\Core\Application\UseCase\GetChainsByFilter;

class ChainsResolver extends ResolverBase {

	public function __construct(
		GetChainsByFilter $get_chains_by_filter,
		UserAccessChecker $user_access_checker
	) {
		$this->get_chains_by_filter = $get_chains_by_filter;
		$this->user_access_checker  = $user_access_checker;
	}

	private GetChainsByFilter $get_chains_by_filter;
	private UserAccessChecker $user_access_checker;

	/**
	 * チェーン一覧を取得します。
	 *
	 * @inheritdoc
	 * @return array
	 */
	public function resolve( array $root_value, array $args ): array {
		$this->user_access_checker->checkHasAdminRole();  // 管理者権限が必要

		$chains = $this->get_chains_by_filter->handle(
			$args['filter']['chainID'],
			$args['filter']['isConnectable']
		);

		return array_map(
			fn( $chain ) => $root_value['chain'](
				$root_value,
				array(
					'chainID' => $chain->id(),
				)
			),
			$chains
		);
	}
}
