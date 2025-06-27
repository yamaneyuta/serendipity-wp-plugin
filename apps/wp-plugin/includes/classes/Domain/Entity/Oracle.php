<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Entity;

use Cornix\Serendipity\Core\Domain\Entity\Chain;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\SymbolPair;

class Oracle {

	public function __construct( Chain $chain, Address $address, SymbolPair $symbol_pair ) {
		$this->chain       = $chain;
		$this->address     = $address;
		$this->symbol_pair = $symbol_pair;
	}

	private Chain $chain;
	private Address $address;
	private SymbolPair $symbol_pair;

	public function chain(): Chain {
		return $this->chain;
	}

	public function address(): Address {
		return $this->address;
	}

	public function symbolPair(): SymbolPair {
		return $this->symbol_pair;
	}

	public function __toString() {
		return json_encode(
			array(
				'chain_ID'     => $this->chain,
				'address'      => $this->address,
				'base_symbol'  => $this->symbol_pair->base()->value(),
				'quote_symbol' => $this->symbol_pair->quote()->value(),
			)
		);
	}
}
