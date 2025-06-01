<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\ValueObject;

use Cornix\Serendipity\Core\Lib\Security\Validate;

/**
 * アドレス(ウォレットアドレス/コントラクトアドレス)を表すクラス
 */
class Address {

	private function __construct( string $address ) {
		Validate::checkAddress( $address );
		$this->address = $address;
	}
	private string $address;

	public function value(): string {
		return $this->address;
	}

	public function __toString() {
		return $this->address;
	}
}
