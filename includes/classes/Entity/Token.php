<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Entity;

use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\ValueObject\Address;
use Cornix\Serendipity\Core\ValueObject\TableRecord\TokenTableRecord;

class Token {

	/** @var Token[] */
	private static array $cache = array();

	private function __construct( int $chain_ID, Address $address, string $symbol, int $decimals, bool $is_payable ) {
		$this->chain_ID   = $chain_ID;
		$this->address    = $address;
		$this->symbol     = $symbol;
		$this->decimals   = $decimals;
		$this->is_payable = $is_payable;
	}

	private int $chain_ID;
	private Address $address;
	private string $symbol;
	private int $decimals;
	private bool $is_payable;

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

	public function isPayable(): bool {
		return $this->is_payable;
	}
	public function setIsPayable( bool $is_payable ): void {
		$this->is_payable = $is_payable;
	}

	public function __toString() {
		return json_encode(
			array(
				'chain_ID'   => $this->chain_ID,
				'address'    => (string) $this->address,
				'symbol'     => $this->symbol,
				'decimals'   => $this->decimals,
				'is_payable' => $this->is_payable,
			)
		);
	}


	public static function from( int $chain_ID, Address $address, string $symbol, int $decimals, bool $payable ): self {
		$cache_key = $chain_ID . $address;

		if ( ! isset( self::$cache[ $cache_key ] ) ) {
			Validate::checkChainID( $chain_ID );
			Validate::checkSymbol( $symbol );
			Validate::checkDecimals( $decimals );
			self::$cache[ $cache_key ] = new Token( $chain_ID, $address, $symbol, $decimals, $payable );
		}

		return self::$cache[ $cache_key ];
	}

	public static function fromTableRecord( TokenTableRecord $record ): self {
		return self::from(
			$record->chain_id,
			Address::from( $record->address ),
			$record->symbol,
			$record->decimals,
			$record->is_payable
		);
	}
}
