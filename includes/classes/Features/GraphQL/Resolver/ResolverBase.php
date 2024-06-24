<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

abstract class ResolverBase {

	public function __construct( string $field ) {
		$this->field = $field;
	}
	private string $field;

	final public function field(): string {
		return $this->field;
	}

	abstract public function resolve( array $root_value, array $args );
}
