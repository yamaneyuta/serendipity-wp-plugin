<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Entity;

use Cornix\Serendipity\Core\Domain\Entity\Chain;
use Cornix\Serendipity\Core\ValueObject\Address;

class Oracle {

	public function __construct( Chain $chain, Address $address, string $base_symbol, string $quote_symbol ) {
		$this->chain        = $chain;
		$this->address      = $address;
		$this->base_symbol  = $base_symbol;
		$this->quote_symbol = $quote_symbol;
	}

	private Chain $chain;
	private Address $address;
	private string $base_symbol;
	private string $quote_symbol;

	public function chain(): Chain {
		return $this->chain;
	}

	public function address(): Address {
		return $this->address;
	}

	public function baseSymbol(): string {
		return $this->base_symbol;
	}

	public function quoteSymbol(): string {
		return $this->quote_symbol;
	}

	public function __toString() {
		return json_encode(
			array(
				'chain_ID'     => $this->chain,
				'address'      => $this->address,
				'base_symbol'  => $this->base_symbol,
				'quote_symbol' => $this->quote_symbol,
			)
		);
	}
}
