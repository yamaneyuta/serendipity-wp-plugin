<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

class ChainResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$chain_id = $args['chainID'];

		return array(
			'id'      => $chain_id,
			'enabled' => true, // TODO: 未実装
		);
	}
}
