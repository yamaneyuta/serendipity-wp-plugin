<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository;

use Cornix\Serendipity\Core\Infrastructure\Format\HexFormat;
use Cornix\Serendipity\Core\Constant\Config;
use Cornix\Serendipity\Core\Domain\Specification\OraclesFilter;
use Cornix\Serendipity\Core\Lib\Transient\TransientFactory;
use Cornix\Serendipity\Core\Infrastructure\Web3\OracleClient;
use Cornix\Serendipity\Core\Domain\ValueObject\Rate;
use Cornix\Serendipity\Core\Domain\ValueObject\SymbolPair;
use Cornix\Serendipity\Core\Infrastructure\Factory\OracleRepositoryFactory;

class RateData {
	public function __construct( RateTransient $rate_transient = null, OracleRate $oracle_rate = null ) {
		$this->rate_transient = $rate_transient ?? new RateTransient();
		$this->oracle_rate    = $oracle_rate ?? new OracleRate();
	}

	private RateTransient $rate_transient;
	private OracleRate $oracle_rate;

	public function get( SymbolPair $symbol_pair ): ?Rate {
		// 一時領域から取得
		$rate = $this->rate_transient->get( $symbol_pair );

		// 一時領域から取得できなかった場合はOracleに問い合わせる
		if ( is_null( $rate ) ) {
			$rate = $this->oracle_rate->get( $symbol_pair );

			// Oracleから取得できた場合は一時領域に保存
			if ( ! is_null( $rate ) ) {
				$this->rate_transient->set( $rate );
			}
		}

		return $rate;
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
		$answer_hex    = HexFormat::toHex( $oracle_client->latestAnswer() );

		return new Rate( $symbol_pair, $answer_hex, $decimals );
	}
}

/**
 * レート情報を一時領域から取得または設定するクラス
 *
 * @internal
 */
class RateTransient {
	public function __construct() {
		$this->transient_factory = new TransientFactory();
	}
	private TransientFactory $transient_factory;

	/**
	 * レート情報を取得します。
	 */
	public function get( SymbolPair $symbol_pair ): ?Rate {
		$rate_amount_hex = $this->transient_factory->rateAmountHex( $symbol_pair )->get( null );
		$rate_decimals   = $this->transient_factory->rateDecimals( $symbol_pair )->get( null );

		if ( ! is_null( $rate_amount_hex ) && ! is_null( $rate_decimals ) ) {
			return new Rate( $symbol_pair, $rate_amount_hex, $rate_decimals );
		}

		return null;
	}

	/**
	 * レート情報を保存します。
	 */
	public function set( Rate $rate ): bool {
		$symbol_pair     = $rate->symbolPair();
		$rate_amount_hex = $rate->amountHex();
		$rate_decimals   = $rate->decimals();

		$ret1 = $this->transient_factory->rateAmountHex( $symbol_pair )->set( $rate_amount_hex, Config::RATE_TRANSIENT_EXPIRATION );
		$ret2 = $this->transient_factory->rateDecimals( $symbol_pair )->set( $rate_decimals, Config::RATE_TRANSIENT_EXPIRATION );

		return $ret1 && $ret2;
	}
}
