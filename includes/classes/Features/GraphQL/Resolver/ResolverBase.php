<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

abstract class ResolverBase {
	abstract public function resolve( array $root_value, array $args );
}
