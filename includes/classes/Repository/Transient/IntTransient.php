<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository\Transient;

class IntTransient {
	public function __construct( string $option_key_name ) {
		$this->transient = new Transient( $option_key_name );
	}

	private Transient $transient;

	public function get( ?int $default = null ): ?int {
		$result = $this->transient->get();
		if ( false === $result ) {
			return $default;
		}
		return (int) $result;
	}

	public function set( int $value, int $expiration = 0 ): bool {
		return $this->transient->set( $value, $expiration );
	}
}
