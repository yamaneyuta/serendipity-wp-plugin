<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository\Option;

class StringOption {
	public function __construct( string $option_key_name ) {
		$this->option = new Option( $option_key_name );
	}

	private Option $option;

	public function get( $default = null ): ?string {
		return $this->option->get( $default );
	}

	public function update( string $value, ?bool $autoload = null ): bool {
		return $this->option->update( $value, $autoload );
	}
}
