<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Entity;

use Cornix\Serendipity\Core\Infrastructure\Database\ValueObject\ChainTableRecord;
use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\ValueObject\Address;
use Cornix\Serendipity\Core\Infrastructure\Database\ValueObject\OracleTableRecord;

class Oracle {

	private function __construct( Chain $chain, Address $address, string $base_symbol, string $quote_symbol ) {
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

	public static function from( Chain $chain, Address $address, string $base_symbol, string $quote_symbol ): Oracle {
		assert( Validate::isSymbol( $base_symbol ), '[CD285CC7] Invalid base symbol. ' . $base_symbol );
		assert( Validate::isSymbol( $quote_symbol ), '[BA65690D] Invalid quote symbol. ' . $quote_symbol );

		return new self( $chain, $address, $base_symbol, $quote_symbol );
	}

	public static function fromTableRecord( OracleTableRecord $oracle_record, ChainTableRecord $chain_record ): self {
		return self::from(
			Chain::fromTableRecord( $chain_record ),
			new Address( $oracle_record->address() ),
			$oracle_record->baseSymbol(),
			$oracle_record->quoteSymbol()
		);
	}
}
