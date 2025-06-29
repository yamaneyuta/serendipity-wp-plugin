<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Calc;

use Cornix\Serendipity\Core\Domain\Entity\Token;
use Cornix\Serendipity\Core\Domain\Specification\TokensFilter;
use Cornix\Serendipity\Core\Domain\Repository\OracleRepository;
use Cornix\Serendipity\Core\Domain\Specification\OraclesFilter;
use Cornix\Serendipity\Core\Repository\RateData;
use Cornix\Serendipity\Core\Infrastructure\Database\Repository\TokenRepositoryImpl;
use Cornix\Serendipity\Core\Domain\ValueObject\Price;
use Cornix\Serendipity\Core\Domain\ValueObject\SymbolPair;
use Cornix\Serendipity\Core\Infrastructure\Factory\OracleRepositoryFactory;
use Cornix\Serendipity\Core\Domain\ValueObject\Symbol;

class PriceExchange {
	public function __construct( RateData $rate_data = null, OracleRepository $oracle_repository = null ) {
		$this->rate_data         = $rate_data ?? new RateData();
		$this->oracle_repository = $oracle_repository ?? ( new OracleRepositoryFactory() )->create();
	}
	private RateData $rate_data;
	private OracleRepository $oracle_repository;

	public function convert( Price $price, string $to_symbol ): Price {
		// 元の価格が0の場合は変換後の値も0
		if ( '0' === $price->amount()->value() ) {
			return new Price( $price->amount(), new Symbol( $to_symbol ) );
		}

		$from_symbol = $price->symbol()->value();
		if ( $from_symbol === $to_symbol ) {
			// 同じ通貨の場合は変換不要
			return $price;
		} elseif ( $this->isConvertible( $from_symbol, $to_symbol ) ) {
			// 直接変換可能な場合
			return $this->convertDirect( $price, $to_symbol );
		} elseif ( $this->isConvertible( $from_symbol, 'USD' ) && $this->isConvertible( 'USD', $to_symbol ) ) {
			// USDを経由して変換可能な場合
			$usd_price = $this->convertDirect( $price, 'USD' );
			return $this->convertDirect( $usd_price, $to_symbol );
		} elseif ( $this->isConvertible( $from_symbol, 'ETH' ) && $this->isConvertible( 'ETH', $to_symbol ) ) {
			// ETHを経由して変換可能な場合
			$eth_price = $this->convertDirect( $price, 'ETH' );
			return $this->convertDirect( $eth_price, $to_symbol );
		} elseif ( $this->isConvertible( $from_symbol, 'ETH' ) && $this->isConvertible( 'ETH', 'USD' ) && $this->isConvertible( 'USD', $to_symbol ) ) {
			// ETH,USDを経由して変換可能な場合
			$eth_price = $this->convertDirect( $price, 'ETH' );
			$usd_price = $this->convertDirect( $eth_price, 'USD' );
			return $this->convertDirect( $usd_price, $to_symbol );
		} elseif ( $this->isConvertible( $from_symbol, 'USD' ) && $this->isConvertible( 'USD', 'ETH' ) && $this->isConvertible( 'ETH', $to_symbol ) ) {
			// USD,ETHを経由して変換可能な場合
			$usd_price = $this->convertDirect( $price, 'USD' );
			$eth_price = $this->convertDirect( $usd_price, 'ETH' );
			return $this->convertDirect( $eth_price, $to_symbol );
		}

		// 未実装
		throw new \Exception( '[61847BDB] Not implemented' );
	}

	private function convertDirect( Price $price, string $to_symbol ): Price {
		$from_symbol = $price->symbol();
		$rate        = $this->rate_data->get( new SymbolPair( $from_symbol, new Symbol( $to_symbol ) ) );

		if ( null !== $rate ) {
			// `1BAT`を`BAT/ETH`で`ETH`に変換するような場合
			$result_amount = $price->amount()->mul( $rate->amount() );
			return new Price( $result_amount, new Symbol( $to_symbol ) );
		} else {
			// `1USD`を`ETH/USD`で`ETH`に変換するような場合
			$rate = $this->rate_data->get( new SymbolPair( new Symbol( $to_symbol ), $from_symbol ) );
			assert( null !== $rate );
			// 除算を行った結果、変換後の最小単位が求められるように、まずは変換後の必要な桁数を取得
			$to_decimals_max = $this->getMaxDecimals( $to_symbol );

			return new Price( $price->amount()->div( $rate->amount(), $to_decimals_max ), new Symbol( $to_symbol ) );
		}
	}

	/**
	 * 指定した通貨シンボルの最大小数点以下桁数を取得します。
	 * ※ ネットワークを跨いだ比較を行い、最大値を取得します。
	 */
	private function getMaxDecimals( string $symbol ): int {
		$tokens_filter = ( new TokensFilter() )->bySymbol( new Symbol( $symbol ) );
		$tokens        = $tokens_filter->apply( ( new TokenRepositoryImpl() )->all() );
		$decimals      = array_map( fn( Token $token ) => $token->decimals(), $tokens );
		return max( $decimals );
	}

	/**
	 * Oracleのレート1つで価格変換が可能かどうかを取得します。
	 * 例1: 販売価格がUSD、購入者がETHで支払う場合(ETH/USDのレートで変換可能)
	 * 例2: 販売価格がLINK、購入者がETHで支払う場合(LINK/ETHのレートで変換可能)
	 * 例3: 販売価格がETH、購入者がLINKで支払う場合(LINK/ETHのレートで変換可能)
	 *
	 * @param string $from_symbol
	 * @param string $to_symbol
	 */
	private function isConvertible( string $from_symbol, string $to_symbol ): bool {
		// [FROM]/[TO]のレートまたは[TO]/[FROM]のOracleが存在する場合は変換可能

		$oracles = $this->oracle_repository->all();

		$from_to_oracle = ( new OraclesFilter() )
			->bySymbolPair( new SymbolPair( new Symbol( $from_symbol ), new Symbol( $to_symbol ) ) )
			->byConnectable()
			->apply( $oracles );
		if ( ! empty( $from_to_oracle ) ) {
			return true; // [FROM]/[TO]のレートが存在する場合は変換可能
		}

		$to_from_oracle = ( new OraclesFilter() )
			->bySymbolPair( new SymbolPair( new Symbol( $to_symbol ), new Symbol( $from_symbol ) ) )
			->byConnectable()
			->apply( $oracles );
		if ( ! empty( $to_from_oracle ) ) {
			return true; // [TO]/[FROM]のレートが存在する場合は変換可能
		}
		// Oracleが存在しない場合は変換不可
		return false;
	}
}
