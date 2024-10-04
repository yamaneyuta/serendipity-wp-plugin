<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Types;

use Cornix\Serendipity\Core\Types\NetworkCategory;

/**
 * ウィジェット(ブロック)の属性を表す型
 */
class WidgetAttributesType {
	public function __construct( ?NetworkCategory $selling_network_category, ?string $selling_amount_hex, ?int $selling_decimals, ?string $selling_symbol ) {
		$this->selling_network_category = $selling_network_category;
		$this->selling_amount_hex       = $selling_amount_hex;
		$this->selling_decimals         = $selling_decimals;
		$this->selling_symbol           = $selling_symbol;
	}

	// プロパティはGraphQLで使用するためcamelCaseで定義

	/** 販売対象のネットワークカテゴリID */
	public ?NetworkCategory $selling_network_category;

	/** 販売価格の値(sellingDecimalsの値と共に使用する) */
	public ?string $selling_amount_hex;

	/** 販売価格の小数点以下桁数 */
	public ?int $selling_decimals;

	/** 販売価格の通貨シンボル */
	public ?string $selling_symbol;


	/**
	 * ブロックの属性オブジェクトからWidgetAttributesTypeを生成します。
	 *
	 * @param array $attrs
	 * @return WidgetAttributesType
	 */
	public static function fromAttrs( array $attrs ): WidgetAttributesType {
		$selling_network_category_id = $attrs['sellingNetworkCategoryID'];
		$selling_network_category    = null === $selling_network_category_id ? null : NetworkCategory::from( $selling_network_category_id );
		$selling_amount_hex          = $attrs['sellingAmountHex'];
		$selling_decimals            = $attrs['sellingDecimals'];
		$selling_symbol              = $attrs['sellingSymbol'];

		return new WidgetAttributesType( $selling_network_category, $selling_amount_hex, $selling_decimals, $selling_symbol );
	}

	/**
	 * WidgetAttributesTypeをブロックの属性オブジェクトに変換します。
	 */
	public function toAttrs(): array {
		return array(
			'sellingNetworkCategoryID' => $this->selling_network_category ? $this->selling_network_category->id() : null,
			'sellingAmountHex'         => $this->selling_amount_hex,
			'sellingDecimals'          => $this->selling_decimals,
			'sellingSymbol'            => $this->selling_symbol,
		);
	}
}
