<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Types;

use Cornix\Serendipity\Core\Types\NetworkCategory;

/**
 * ウィジェット(ブロック)の属性を表す型
 */
class WidgetAttributesType {
	public function __construct( ?NetworkCategory $selling_network_category, ?string $selling_amount_hex, ?int $selling_decimals, ?string $selling_symbol ) {
		$this->sellingNetworkCategoryID = $selling_network_category->id();
		$this->sellingAmountHex         = $selling_amount_hex;
		$this->sellingDecimals          = $selling_decimals;
		$this->sellingSymbol            = $selling_symbol;

		// プロパティが既定値と一致する場合、値がpost_contentに保存されないため、`src/block/index.ts`で指定しているデフォルト値を設定
		if ( null === $this->sellingAmountHex ) {
			$this->sellingAmountHex = '0x0';
		}
		if ( null === $this->sellingDecimals ) {
			$this->sellingDecimals = 0;
		}
	}

	// プロパティはGraphQLで使用するためcamelCaseで定義

	/** 販売対象のネットワークカテゴリID */
	public ?int $sellingNetworkCategoryID;

	/** 販売価格の値(sellingDecimalsの値と共に使用する) */
	public ?string $sellingAmountHex;

	/** 販売価格の小数点以下桁数 */
	public ?int $sellingDecimals;

	/** 販売価格の通貨シンボル */
	public ?string $sellingSymbol;
}
