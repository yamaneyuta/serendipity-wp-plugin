<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\ValueObject;

/**
 * @extends ObjectValueArrayBase<Address>
 */
final class Addresses extends ObjectValueArrayBase {

	/**
	 * @param Address[] $addresses
	 */
	private function __construct( array $addresses ) {
		parent::__construct( $addresses );
	}

	/**
	 * string型のアドレスの配列からインスタンスを生成します
	 *
	 * @param string|string[] $address_values
	 */
	public static function fromAddressValues( $address_values ): Addresses {
		$address_values = is_array( $address_values ) ? $address_values : array( $address_values );
		$addresses      = array_map(
			fn( string $address_value ) => Address::from( $address_value ),
			$address_values
		);
		return self::from( $addresses );
	}

	/**
	 * @param Address|Address[] $addresses
	 */
	public static function from( $addresses ): Addresses {
		$addresses = is_array( $addresses ) ? $addresses : array( $addresses );
		foreach ( $addresses as $addr ) {
			if ( ! ( $addr instanceof Address ) ) {
				throw new \InvalidArgumentException( '[461C2300] Only Address instances allowed.' );
			}
		}
		return new self( $addresses );
	}

	public function contains( Address $address ): bool {
		return null !== $this->find( fn( Address $addr ) => $addr->equals( $address ) );
	}

	/**
	 * アドレスの文字列一覧を取得します
	 */
	public function values(): array {
		return array_map( fn( Address $address ) => $address->value(), $this->toArray() );
	}
}
