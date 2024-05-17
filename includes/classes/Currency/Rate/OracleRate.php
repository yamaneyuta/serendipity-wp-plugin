<?php

declare(strict_types=1);

namespace Cornix\Serendipity\Core\Currency\Rate;

use Cornix\Serendipity\Core\Logger\Logger;
use Cornix\Serendipity\Core\Web3\DataType\OracleLatestData;
use Cornix\Serendipity\Core\Web3\IContract;

/**
 * ブロックチェーンのOracleから価格のレートを取得するクラス
 */
class OracleRate implements IRate {

	public function __construct( IContract $contract, string $from, string $to ) {
		$this->contract = $contract;
		$this->from     = $from;
		$this->to       = $to;
	}
	/** @var IContract */
	private $contract;

	/** @var string */
	private $from;

	/** @var string */
	private $to;

	/** @var OracleLatestData[]|null */
	private $oracleLatestData = null;

	/** @inheritdoc */
	public function getRateAmountHex( string $symbol ): string {
		return $this->getOracleLatestData( $symbol )->answer_hex;
	}

	/** @inheritdoc */
	public function getRateDecimals( string $symbol ): int {
		return $this->getOracleLatestData( $symbol )->decimals;
	}

	/**
	 * @return OracleLatestData
	 */
	protected function getOracleLatestData( string $symbol ): OracleLatestData {
		if ( $symbol === $this->from ) {
			$index = 0;
		} elseif ( $symbol === $this->to ) {
			$index = 1;
		} else {
			Logger::error( "symbol: $symbol" );
			throw new \Exception( '{DDCF862B-BA05-4503-8B46-B40D2BC6D307}' );
		}

		if ( is_null( $this->oracleLatestData ) ) {
			$this->oracleLatestData = $this->contract->getOracleLatestData( array( $this->from, $this->to ) );
		}

		return $this->oracleLatestData[ $index ];
	}
}
