<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\ValueObject;

use Cornix\Serendipity\Core\Lib\Convert\Padding;
use Cornix\Serendipity\Core\Lib\Strings\Strings;

/**
 * アドレス(ウォレットアドレス/コントラクトアドレス)を表すクラス
 */
final class Address {

	public function __construct( string $address_value ) {
		self::checkValidAddressFormat( $address_value );
		// アドレスは常にチェックサムアドレスで保持する
		$this->address_value = \Web3\Utils::toChecksumAddress( $address_value );
	}
	private string $address_value;

	public static function from( ?string $address_value ): ?self {
		return is_null( $address_value ) ? null : new self( $address_value );
	}

	public function value(): string {
		return $this->address_value;
	}

	public function toBytes32Hex(): string {
		return ( new Padding() )->toBytes32Hex( $this->value() );
	}

	public function __toString() {
		return $this->address_value;
	}

	public function equals( Address $other ): bool {
		return $this->address_value === $other->address_value;
	}

	private static function checkValidAddressFormat( string $address_value ): void {
		// 本アプリでは`0x`プレフィックスを必須とする
		$is_valid = Strings::starts_with( $address_value, '0x' ) && \Web3\Utils::isAddress( $address_value );
		if ( ! $is_valid ) {
			throw new \InvalidArgumentException( '[B4AE59FC] Invalid address format. ' . $address_value );
		}
	}
}
