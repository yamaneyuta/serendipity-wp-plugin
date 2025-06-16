<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Entity;

use Cornix\Serendipity\Core\Domain\Entity\Chain;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\BlockNumber;
use Cornix\Serendipity\Core\Domain\ValueObject\UnixTimestamp;

class AppContract {
	protected function __construct( Chain $chain, Address $address, ?BlockNumber $crawled_block_number, ?UnixTimestamp $crawled_block_number_updated_at ) {
		$this->chain                           = $chain;
		$this->address                         = $address;
		$this->crawled_block_number            = $crawled_block_number;
		$this->crawled_block_number_updated_at = $crawled_block_number_updated_at;
	}

	private Chain $chain;
	private Address $address;
	private ?BlockNumber $crawled_block_number;
	private ?UnixTimestamp $crawled_block_number_updated_at;

	public function chain(): Chain {
		return $this->chain;
	}
	public function address(): Address {
		return $this->address;
	}
	public function crawledBlockNumber(): ?BlockNumber {
		return $this->crawled_block_number;
	}
	public function setCrawledBlockNumber( BlockNumber $crawled_block_number ): void {
		$this->crawled_block_number            = $crawled_block_number;
		$this->crawled_block_number_updated_at = UnixTimestamp::now();
	}
	public function crawledBlockNumberUpdatedAt(): ?UnixTimestamp {
		return $this->crawled_block_number_updated_at;
	}
}
