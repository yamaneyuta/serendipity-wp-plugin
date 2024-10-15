<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Repository\PayableTokens;
use Cornix\Serendipity\Core\Lib\Security\Judge;

class ChainResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$chain_ID = $args['chainID'];

		$payable_tokens_callback = function () use ( $root_value, $chain_ID ) {
			Judge::checkHasAdminRole(); // 管理者権限が必要
			$payable_tokens = ( new PayableTokens() )->get( $chain_ID );

			return array_map(
				function ( $token ) use ( $root_value ) {
					return $root_value['token'](
						$root_value,
						array(
							'chainID' => $token->chainID(),
							'address' => $token->address(),
						)
					);
				},
				$payable_tokens
			);
		};

		return array(
			'id'            => $chain_ID,
			'payableTokens' => $payable_tokens_callback,
		);
	}
}
