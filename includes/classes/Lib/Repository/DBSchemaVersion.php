<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Option\Option;
use Cornix\Serendipity\Core\Lib\Repository\Option\OptionKey;

class DBSchemaVersion {
	public function __construct() {
		$this->option = new Option( OptionKey::DB_SCHEMA_VERSION );
	}
	private Option $option;

	public function get(): ?string {
		return $this->option->get( '0.0.0' );
	}

	public function set( string $value ): bool {
		// インストール時やアップデート時など、利用頻度が限定されるため、`autoload`は`false`を指定
		return $this->option->update( $value, false );
	}
}
