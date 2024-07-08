<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository\Option;

class Option {
	public function __construct( string $key ) {
		$prefix            = OptionKey::prefix();
		$this->option_name = $prefix . $key;
	}
	private string $option_name;

	public function get( $default = false ) {
		return get_option( $this->option_name, $default );
	}

	public function update( $value, $autoload = null ): bool {
		$success = update_option( $this->option_name, $value, $autoload );
		assert( true === $success );
		return $success;
	}
}
