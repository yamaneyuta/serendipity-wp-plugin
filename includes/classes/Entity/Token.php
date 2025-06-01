<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Entity;

use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\ValueObject\Address;

class Token {

	/** @var Token[] */
	private static array $cache = array();

	private function __construct( int $chain_ID, Address $address, string $symbol, int $decimals ) {
		$this->chain_ID = $chain_ID;
		$this->address  = $address;
		$this->symbol   = $symbol;
		$this->decimals = $decimals;
	}

	private int $chain_ID;
	private Address $address;
	private string $symbol;
	private int $decimals;

	public function chainID(): int {
		return $this->chain_ID;
	}

	public function address(): Address {
		return $this->address;
	}

	public function symbol(): string {
		return $this->symbol;
	}

	public function decimals(): int {
		return $this->decimals;
	}

	public function __toString() {
		return json_encode(
			array(
				'chain_ID' => $this->chain_ID,
				'address'  => $this->address,
				'symbol'   => $this->symbol,
				'decimals' => $this->decimals,
			)
		);
	}


	public static function from( int $chain_ID, Address $address, string $symbol, int $decimals ): Token {
		$cache_key = $chain_ID . $address;

		if ( ! isset( self::$cache[ $cache_key ] ) ) {
			Validate::checkChainID( $chain_ID );
			Validate::checkSymbol( $symbol );
			Validate::checkDecimals( $decimals );
			self::$cache[ $cache_key ] = new Token( $chain_ID, $address, $symbol, $decimals );
		}

		return self::$cache[ $cache_key ];
	}
}
