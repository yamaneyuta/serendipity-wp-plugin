<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\Factory\ServerSignerServiceFactory;

class ServerSignerResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		$server_signer = ( new ServerSignerServiceFactory() )->create()->getServerSigner();
		return array(
			'address' => fn() => $server_signer->address()->value(),
		);
	}
}
