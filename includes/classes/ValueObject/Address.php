<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\ValueObject;

use Cornix\Serendipity\Core\Lib\Security\Validate;

/**
 * アドレス(ウォレットアドレス/コントラクトアドレス)を表すクラス
 */
class Address {

	public function __construct( string $address ) {
		Validate::checkAddressFormat( $address );
		$this->address = $address;
	}
	private string $address;

	public static function from( ?string $address ): ?self {
		return is_null( $address ) ? null : new self( $address );
	}

	public function value(): string {
		return $this->address;
	}

	public function __toString() {
		return $this->address;
	}

	public function equals( Address $other ): bool {
		return $this->address === $other->address;
	}
}
