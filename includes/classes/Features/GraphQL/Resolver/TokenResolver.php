<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Repository\PayableTokens;
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

		$is_payable_callback = ( new PayableTokens() )->exists( $token );

		return array(
			'chain'     => $root_value['Chain']( $root_value, array( 'chainID' => $chain_id ) ),
			'address'   => $token->address(),
			'symbol'    => fn() => $token->symbol(),
			'isPayable' => $is_payable_callback,
		);
	}
}
