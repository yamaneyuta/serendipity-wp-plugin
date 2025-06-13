<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Entity;

use Cornix\Serendipity\Core\Domain\Entity\Chain;
use Cornix\Serendipity\Core\ValueObject\Address;
use Cornix\Serendipity\Core\ValueObject\BlockNumber;

class AppContract {
	protected function __construct( Chain $chain, Address $address, ?BlockNumber $activation_block_number = null, ?BlockNumber $crawled_block_number = null ) {
		$this->chain                   = $chain;
		$this->address                 = $address;
		$this->activation_block_number = $activation_block_number;
		$this->crawled_block_number    = $crawled_block_number;
	}

	private Chain $chain;
	private Address $address;
	private ?BlockNumber $activation_block_number;
	private ?BlockNumber $crawled_block_number;

	public function chain(): Chain {
		return $this->chain;
	}
	public function address(): Address {
		return $this->address;
	}
	public function activationBlockNumber(): ?BlockNumber {
		return $this->activation_block_number;
	}
	public function crawledBlockNumber(): ?BlockNumber {
		return $this->crawled_block_number;
	}
}
