<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository\Option;

class ArrayOption {
	public function __construct( string $option_key_name ) {
		$this->option = new Option( $option_key_name );
	}

	private Option $option;

	public function get( $default = null ): ?array {
		$ret = $this->option->get( $default );
		return is_null( $ret ) ? null : $ret;
	}

	public function update( array $value, ?bool $autoload = null ): bool {
		return $this->option->update( $value, $autoload );
	}
}
