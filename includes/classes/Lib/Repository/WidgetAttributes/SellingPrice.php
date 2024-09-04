<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository\WidgetAttributes;

use Cornix\Serendipity\Core\Types\PriceType;
use Cornix\Serendipity\Core\Types\WidgetAttributesType;

class SellingPrice {

	/**
	 * ウィジェット(ブロック)の属性値から販売価格を取得します。
	 */
	public static function fromWidgetAttributes( WidgetAttributesType $widgetAttributes ): ?PriceType {
		$amount_hex = $widgetAttributes->sellingAmountHex;
		$decimals   = $widgetAttributes->sellingDecimals;
		$symbol     = $widgetAttributes->sellingSymbol;

		// nullが含まれる場合はnullを返す
		if ( is_null( $amount_hex ) || is_null( $decimals ) || is_null( $symbol ) ) {
			return null;
		}

		// 価格の型に変換して返す
		return new PriceType( $amount_hex, $decimals, $symbol );
	}
}
