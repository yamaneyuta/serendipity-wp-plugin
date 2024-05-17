<?php

declare(strict_types=1);

namespace Cornix\Serendipity\Core\Currency\Price;

use Cornix\Serendipity\Core\Currency\Rate\IRate;
use Cornix\Serendipity\Core\Currency\Symbol\Symbol;
use Cornix\Serendipity\Core\Utils\BNConvert;

class Price {

	public static function convert( Symbol $symbol_info, IRate $rate, string $from_symbol, string $from_display_amount_hex, int $from_display_decimals, string $to_symbol ): string {
		// $chain_id, $from_symbol, $from_display_amount_hex, $from_display_decimals, $to_symbol, $actual_to_decimals

		$from_oracle_answer_hex = $rate->getRateAmountHex( $from_symbol );
		$from_oracle_decimals   = $rate->getRateDecimals( $from_symbol );
		$to_oracle_answer_hex   = $rate->getRateAmountHex( $to_symbol );
		$to_oracle_decimals     = $rate->getRateDecimals( $to_symbol );
		$to_decimals            = $symbol_info->getDecimals( $to_symbol );

		// 最終的な小数点以下桁数を計算
		$total_decimals =
			$from_display_decimals  // 販売価格で設定されている通貨の小数点以下桁数
			+ $from_oracle_decimals
			- $to_oracle_decimals
			- $to_decimals;  // 変換後の通貨で設定されている小数点以下桁数

		// bcmulやbcdivを使うため、10進数の文字列に変換する
		$from_display_amount_dec = BNConvert::hexToDecString( $from_display_amount_hex );
		$from_oracle_answer_dec  = BNConvert::hexToDecString( $from_oracle_answer_hex );
		$correct_decimals_dec    = bcpow( '10', (string) abs( $total_decimals ), 0 );
		$to_oracle_answer_dec    = BNConvert::hexToDecString( $to_oracle_answer_hex );

		// 通貨の変換
		if ( $total_decimals >= 0 ) {
			$result_dec = bcmul( $from_display_amount_dec, $from_oracle_answer_dec, 0 );
			$result_dec = bcdiv( $result_dec, $correct_decimals_dec, 0 );
			$result_dec = bcdiv( $result_dec, $to_oracle_answer_dec, 0 );
		} else {
			$result_dec = bcmul( $from_display_amount_dec, $from_oracle_answer_dec, 0 );
			$result_dec = bcmul( $result_dec, $correct_decimals_dec, 0 );
			$result_dec = bcdiv( $result_dec, $to_oracle_answer_dec, 0 );
		}

		return BNConvert::decStringToHex( $result_dec );
	}
}
