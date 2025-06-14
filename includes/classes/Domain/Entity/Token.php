<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Entity;

use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\ValueObject\Address;
use Cornix\Serendipity\Core\Infrastructure\Database\ValueObject\TokenTableRecord;
use Cornix\Serendipity\Core\ValueObject\ChainID;

class Token {

	public function __construct( ChainID $chain_ID, Address $address, string $symbol, int $decimals, bool $is_payable ) {
		Validate::checkSymbol( $symbol );
		Validate::checkDecimals( $decimals );

		$this->chain_ID   = $chain_ID;
		$this->address    = $address;
		$this->symbol     = $symbol;
		$this->decimals   = $decimals;
		$this->is_payable = $is_payable;
	}

	private ChainID $chain_ID;
	private Address $address;
	private string $symbol;
	private int $decimals;
	private bool $is_payable;

	public function chainID(): ChainID {
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
				'chain_ID'   => $this->chain_ID->value(),
				'address'    => (string) $this->address,
				'symbol'     => $this->symbol,
				'decimals'   => $this->decimals,
				'is_payable' => $this->is_payable,
			)
		);
	}

	public static function fromTableRecord( TokenTableRecord $record ): self {
		return new self(
			new ChainID( $record->chainID() ),
			Address::from( $record->address() ),
			$record->symbol(),
			$record->decimals(),
			$record->isPayable()
		);
	}
}
