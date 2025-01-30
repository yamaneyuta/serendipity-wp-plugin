<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Types;

use Cornix\Serendipity\Core\Lib\Security\Judge;

class OracleType {

	/** @var OracleType[] */
	private static array $cache = array();

	private function __construct( int $chain_ID, string $oracle_address, string $base_symbol, string $quote_symbol ) {
		$this->chain_ID       = $chain_ID;
		$this->oracle_address = $oracle_address;
		$this->base_symbol    = $base_symbol;
		$this->quote_symbol   = $quote_symbol;
	}

	private int $chain_ID;
	private string $oracle_address;
	private string $base_symbol;
	private string $quote_symbol;

	public function chainID(): int {
		return $this->chain_ID;
	}

	public function oracleAddress(): string {
		return $this->oracle_address;
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
				'chain_ID'       => $this->chain_ID,
				'oracle_address' => $this->oracle_address,
				'base_symbol'    => $this->base_symbol,
				'quote_symbol'   => $this->quote_symbol,
			)
		);
	}

	public static function from( int $chain_ID, string $oracle_address, string $base_symbol, string $quote_symbol ): OracleType {
		assert( Judge::isChainID( $chain_ID ), '[403AD6AB] Invalid chain ID. chain id: ' . $chain_ID );
		assert( Judge::isAddress( $oracle_address ), '[7A82CB13] Invalid oracle address. chain id: ' . $chain_ID . ', address: ' . $oracle_address );
		assert( Judge::isSymbol( $base_symbol ), '[CD285CC7] Invalid base symbol. ' . $base_symbol );
		assert( Judge::isSymbol( $quote_symbol ), '[BA65690D] Invalid quote symbol. ' . $quote_symbol );

		if ( is_null( self::$cache[ $chain_ID ][ $oracle_address ] ?? null ) ) {
			self::$cache[ $chain_ID ][ $oracle_address ] = new OracleType( $chain_ID, $oracle_address, $base_symbol, $quote_symbol );
		}

		return self::$cache[ $chain_ID ][ $oracle_address ];
	}
}
