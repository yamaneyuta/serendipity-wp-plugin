<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository\Option;

use Cornix\Serendipity\Core\Lib\Repository\Name\Prefix;
use Cornix\Serendipity\Core\Lib\Strings\Strings;

class Option {
	public function __construct( string $option_key_name ) {
		assert( 0 === Strings::strpos( $option_key_name, ( new Prefix() )->optionKeyPrefix() ) );
		$this->option_key_name = $option_key_name;
	}
	private string $option_key_name;

	public function get( $default = false ) {
		return get_option( $this->option_key_name, $default );
	}

	/**
	 * 値を更新します
	 *
	 * @param mixed     $value
	 * @param null|bool $autoload
	 */
	public function update( $value, ?bool $autoload = null ): void {
		update_option( $this->option_key_name, $value, $autoload );
	}

	public function delete(): bool {
		$success = delete_option( $this->option_key_name );
		assert( true === $success );
		return $success;
	}
}
