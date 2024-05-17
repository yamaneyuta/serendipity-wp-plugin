<?php

declare(strict_types=1);

namespace Cornix\Serendipity\Core\Currency\Rate;

use Cornix\Serendipity\Core\Web3\DataType\OracleLatestData;
use Cornix\Serendipity\Core\Web3\IContract;

class CachedOracleRate extends OracleRate {

	public function __construct( IContract $contract, string $from, string $to ) {
		parent::__construct( $contract, $from, $to );

		$this->chain_id = $contract->getChainId();
	}

	/** @var int */
	private $chain_id;

	/** @inheritdoc */
	protected function getOracleLatestData( string $symbol ): OracleLatestData {
		$result = parent::getOracleLatestData( $symbol );

		// TODO: Cache

		return $result;
	}
}
