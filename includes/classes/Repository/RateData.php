<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository;

use Cornix\Serendipity\Core\Domain\Specification\OraclesFilter;
use Cornix\Serendipity\Core\Domain\ValueObject\Amount;
use Cornix\Serendipity\Core\Infrastructure\Web3\OracleClient;
use Cornix\Serendipity\Core\Domain\ValueObject\Rate;
use Cornix\Serendipity\Core\Domain\ValueObject\SymbolPair;
use Cornix\Serendipity\Core\Infrastructure\Factory\OracleRepositoryFactory;

class RateData {
	public function __construct( OracleRate $oracle_rate = null ) {
		$this->oracle_rate = $oracle_rate ?? new OracleRate();
	}

	private OracleRate $oracle_rate;

	public function get( SymbolPair $symbol_pair ): ?Rate {
		return $this->oracle_rate->get( $symbol_pair );
	}
}

/**
 * Oracleから通貨レートを取得するクラス
 */
class OracleRate {
	public function get( SymbolPair $symbol_pair ): ?Rate {
		$connectable_oracles = ( new OraclesFilter() )
			->bySymbolPair( $symbol_pair )
			->byConnectable()
			->apply( ( new OracleRepositoryFactory() )->create()->all() );
		$connectable_oracle  = array_values( $connectable_oracles )[0] ?? null;

		if ( null === $connectable_oracle ) {
			// 指定した通貨ペアのOracleが存在しない場合はnullを返す
			return null;
		}

		// Oracleコントラクトから値を取得
		$oracle_client = new OracleClient( $connectable_oracle->chain()->rpcURL(), $connectable_oracle );
		$decimals      = $oracle_client->decimals();
		$answer_amount = Amount::from( $oracle_client->latestAnswer()->toString() );
		$rate_amount   = $answer_amount->mul( Amount::from( (string) ( 10 ** $decimals ) ) );

		return new Rate( $symbol_pair, $rate_amount );
	}
}
