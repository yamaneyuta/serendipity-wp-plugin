<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Calc;

use Cornix\Serendipity\Core\Service\OracleService;
use Cornix\Serendipity\Core\Repository\RateData;
use Cornix\Serendipity\Core\Repository\TokenData;
use Cornix\Serendipity\Core\ValueObject\Price;
use Cornix\Serendipity\Core\ValueObject\SymbolPair;
use phpseclib\Math\BigInteger;

class PriceExchange {
	public function __construct( RateData $rate_data = null, OracleService $oracle = null ) {
		$this->rate_data = $rate_data ?? new RateData();
		$this->oracle    = $oracle ?? new OracleService();
	}
	private RateData $rate_data;
	private OracleService $oracle;

	public function convert( Price $price, string $to_symbol ): Price {
		// 元の価格が0の場合は変換後の値も0
		if ( Hex::isZero( $price->amountHex() ) ) {
			return new Price( $price->amountHex(), $price->decimals(), $to_symbol );
		}

		$from_symbol = $price->symbol();
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
		$rate        = $this->rate_data->get( new SymbolPair( $from_symbol, $to_symbol ) );

		if ( null !== $rate ) {
			// `1BAT`を`BAT/ETH`で`ETH`に変換するような場合
			$amount          = new BigInteger( $price->amountHex(), 16 );
			$result_amount   = $amount->multiply( new BigInteger( $rate->amountHex(), 16 ) );
			$result_decimals = $price->decimals() + $rate->decimals();
			return new Price( Hex::from( $result_amount ), $result_decimals, $to_symbol );
		} else {
			// `1USD`を`ETH/USD`で`ETH`に変換するような場合
			$rate = $this->rate_data->get( new SymbolPair( $to_symbol, $from_symbol ) );
			assert( null !== $rate );
			// 除算を行った結果、変換後の最小単位が求められるように、まずは変換後の必要な桁数を取得
			$to_decimals_max = $this->getMaxDecimals( $to_symbol );

			$price_amount_hex = $price->amountHex();
			$price_decimals   = $price->decimals();
			// 変換後の通貨シンボルで最小単位が求められるように、変換前の価格の桁数を調整
			$diff_decimals = ( $to_decimals_max + $rate->decimals() ) - $price->decimals();
			if ( $diff_decimals > 0 ) {
				$price_amount_hex = Hex::from( ( new BigInteger( $price_amount_hex, 16 ) )->multiply( new BigInteger( '1' . str_repeat( '0', $diff_decimals ), 10 ) ) );
				$price_decimals  += $diff_decimals;
			}

			$result_amount   = ( new BigInteger( $price_amount_hex, 16 ) )->divide( new BigInteger( $rate->amountHex(), 16 ) )[0]; // 商のみ取得
			$result_decimals = $price_decimals - $rate->decimals();

			return new Price( Hex::from( $result_amount ), $result_decimals, $to_symbol );
		}
	}

	/**
	 * 指定した通貨シンボルの最大小数点以下桁数を取得します。
	 * ※ ネットワークを跨いだ比較を行い、最大値を取得します。
	 */
	private function getMaxDecimals( string $symbol ): int {
		$token_data = ( new TokenData() )->select( null, null, $symbol );
		$decimals   = array_map( fn( $token ) => $token->decimals(), $token_data );
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
		return ! empty( $this->oracle->connectableChainIDs( new SymbolPair( $from_symbol, $to_symbol ) ) ) ||
			! empty( $this->oracle->connectableChainIDs( new SymbolPair( $to_symbol, $from_symbol ) ) );
	}
}
