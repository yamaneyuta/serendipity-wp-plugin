<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Option;

class IntOption {
	public function __construct( string $option_key_name ) {
		$this->option = new Option( $option_key_name );
	}

	private Option $option;

	public function get( $default = null ): ?int {
		$ret = $this->option->get( $default );
		return is_null( $ret ) ? null : (int) $ret;
	}

	public function update( int $value, ?bool $autoload = null ): void {
		$this->option->update( (string) $value, $autoload );
		assert( $value === $this->get( $value ) );
	}
}
