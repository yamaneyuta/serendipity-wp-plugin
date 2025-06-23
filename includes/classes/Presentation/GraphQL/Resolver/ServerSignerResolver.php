<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\Service\ServerSignerService;

class ServerSignerResolver extends ResolverBase {

	public function __construct( ServerSignerService $server_signer_service ) {
		$this->server_signer_service = $server_signer_service;
	}

	private ServerSignerService $server_signer_service;

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		return array(
			'address' => fn() => $this->server_signer_service->getServerSigner()->address()->value(),
		);
	}
}
