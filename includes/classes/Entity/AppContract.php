<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Entity;

use Cornix\Serendipity\Core\ValueObject\Address;

class AppContract {
	public function __construct( Chain $chain, Address $address ) {
		$this->chain   = $chain;
		$this->address = $address;
	}

	private Chain $chain;
	private Address $address;

	public function chain(): Chain {
		return $this->chain;
	}
	public function address(): Address {
		return $this->address;
	}
}
