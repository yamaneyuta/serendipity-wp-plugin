<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Repository\PayableTokens;
use Cornix\Serendipity\Core\Lib\Security\Judge;
use Cornix\Serendipity\Core\Types\Token;

class TokenResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$chain_id = $args['chainID'];
		/** @var string */
		$address = $args['address'];

		$token = Token::from( $chain_id, $address );

		$is_payable_callback = function () use ( $token ) {
			Judge::checkHasAdminRole();  // 管理者権限が必要
			return ( new PayableTokens() )->exists( $token );
		};

		return array(
			'chain'     => fn() => $root_value['chain']( $root_value, array( 'chainID' => $chain_id ) ),
			'address'   => $address,
			'symbol'    => fn() => $token->symbol(),
			'isPayable' => $is_payable_callback,
		);
	}
}