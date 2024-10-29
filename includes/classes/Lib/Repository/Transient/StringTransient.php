<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository\Transient;

class StringTransient {
	public function __construct( string $option_key_name ) {
		$this->transient = new Transient( $option_key_name );
	}

	private Transient $transient;

	public function get( $default = null ): ?string {
		$result = $this->transient->get();
		if ( false === $result ) {
			return $default;
		}
		return $result;
	}

	public function set( string $value, int $expiration = 0 ): bool {
		return $this->transient->set( $value, $expiration );
	}
}
