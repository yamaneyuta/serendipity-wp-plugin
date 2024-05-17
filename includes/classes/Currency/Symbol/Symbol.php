<?php

declare(strict_types=1);

namespace Cornix\Serendipity\Core\Currency\Symbol;

use Cornix\Serendipity\Core\Logger\Logger;
use Cornix\Serendipity\Core\Web3\IContract;

class Symbol implements ISymbol {
	public function __construct( IContract $contract ) {
		$this->contract = $contract;
	}
	/** @var IContract */
	private $contract;

	public function getDecimals( string $symbol ): int {
		$payable_symbols_info = $this->contract->getPayableSymbolsInfo();

		$symbols  = $payable_symbols_info['symbols'];
		$decimals = $payable_symbols_info['decimals'];

		$symbol_index = array_search( $symbol, $symbols, true );
		if ( $symbol_index >= 0 ) {
			return $decimals[ $symbol_index ];
		}

		Logger::error( 'Symbol not found: ' . $symbol . ', payable_symbols_info: ' . json_encode( $payable_symbols_info ) );
		throw new \Exception( '{253334E5-477E-41EE-B38F-F5FC645BB680}' );
	}
}
