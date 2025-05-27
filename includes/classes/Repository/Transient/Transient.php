<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository\Transient;

use Cornix\Serendipity\Core\Repository\Name\Prefix;
use Cornix\Serendipity\Core\Lib\Strings\Strings;

class Transient {
	public function __construct( string $transient_key_name ) {
		assert( 0 === Strings::strpos( $transient_key_name, ( new Prefix() )->transientKeyPrefix() ) );
		$this->transient_key_name = $transient_key_name;
	}
	private string $transient_key_name;

	public function get() {
		// キーが存在しない場合や期限切れの場合はfalseを返すことに注意
		return get_transient( $this->transient_key_name );
	}

	public function set( $value, int $expiration = 0 ): bool {
		$success = set_transient( $this->transient_key_name, $value, $expiration );
		assert( true === $success );
		return $success;
	}
}
