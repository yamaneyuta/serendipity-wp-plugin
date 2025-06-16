<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Infrastructure\Database\Repository\TokenRepositoryImpl;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;

class TokenResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		$chain_id = new ChainID( $args['chainID'] );
		$address  = Address::from( $args['address'] ?? null );

		if ( null === $address ) {
			throw new \InvalidArgumentException( '[C0B26B53] Invalid address provided.' );
		}

		$token = ( new TokenRepositoryImpl() )->get( $chain_id, $address );

		return array(
			'chain'     => fn() => $root_value['chain']( $root_value, array( 'chainID' => $chain_id->value() ) ),
			'address'   => $address->value(),
			'symbol'    => fn() => $token->symbol(),
			'isPayable' => fn() => $token->isPayable(),
		);
	}
}
