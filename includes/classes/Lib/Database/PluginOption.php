<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Lib\SystemInfo\PluginInfo;

class PluginOption {

	public function __construct() {
		$this->prefix = ( new PluginInfo() )->optionNamePrefix();
	}

	private string $prefix;

	public function set( string $option, $value, $autoload = null ) {
		return update_option( $this->prefix . $option, $value, $autoload );
	}

	public function get( string $option_name, $default = false ) {
		return get_option( $this->prefix . $option_name, $default );
	}
}
