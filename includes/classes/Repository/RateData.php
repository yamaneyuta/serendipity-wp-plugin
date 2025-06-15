<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository;

use Cornix\Serendipity\Core\Infrastructure\Format\HexFormat;
use Cornix\Serendipity\Core\Service\OracleService;
use Cornix\Serendipity\Core\Constant\Config;
use Cornix\Serendipity\Core\Domain\Entity\Oracle;
use Cornix\Serendipity\Core\Lib\Transient\TransientFactory;
use Cornix\Serendipity\Core\Infrastructure\Web3\OracleClient;
use Cornix\Serendipity\Core\Service\Factory\ChainServiceFactory;
use Cornix\Serendipity\Core\Domain\ValueObject\Rate;
use Cornix\Serendipity\Core\Domain\ValueObject\SymbolPair;

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
		// 指定した通貨ペアのOracleがデプロイされているチェーンID一覧を取得
		// TODO: 本番環境とテスト環境で同じ順でOracleへの問い合わせでよいか確認
		$chain_IDs = ( new OracleService() )->connectableChainIDs( $symbol_pair );

		foreach ( $chain_IDs as $chain_ID ) {
			// コントラクトアドレスを取得
			$contract_address = ( new OracleService() )->address( $chain_ID, $symbol_pair );
			assert( ! is_null( $contract_address ), "[460973B3] chain id: {$chain_ID}, symbol pair: {$symbol_pair}" );    // 最初に通貨ペアで絞り込んだチェーンIDを元にアドレスを取得しているため、必ず取得できる

			$chain = ( new ChainServiceFactory() )->create( $GLOBALS['wpdb'] )->getChain( $chain_ID );
			if ( $chain->connectable() ) {
				// Oracleに問い合わせ
				$oracle        = new Oracle( $chain, $contract_address, $symbol_pair->base(), $symbol_pair->quote() );
				$oracle_client = new OracleClient( $chain->rpcURL(), $oracle );
				$decimals      = $oracle_client->decimals();
				$answer_hex    = HexFormat::toHex( $oracle_client->latestAnswer() );

				return new Rate( $symbol_pair, $answer_hex, $decimals );
			}
		}

		return null;
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
