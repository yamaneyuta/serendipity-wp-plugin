<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\UseCase\GetChainsByFilter;
use Cornix\Serendipity\Core\Lib\Security\Validate;

class ChainsResolver extends ResolverBase {

	public function __construct( GetChainsByFilter $get_chains_by_filter ) {
		$this->get_chains_by_filter = $get_chains_by_filter;
	}

	private GetChainsByFilter $get_chains_by_filter;

	/**
	 * チェーン一覧を取得します。
	 *
	 * @inheritdoc
	 * @return array
	 */
	public function resolve( array $root_value, array $args ): array {
		Validate::checkHasAdminRole();  // 管理者権限が必要

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
