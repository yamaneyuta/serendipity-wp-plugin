<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Types;

/**
 * ウィジェット(ブロック)の属性を表す型
 */
class WidgetAttributesType {
	public function __construct( PriceType $price, string $selling_network ) {
		$this->sellingPrice   = $price;
		$this->sellingNetwork = $selling_network;
	}

	// プロパティはGraphQLで使用するためcamelCase

	public PriceType $sellingPrice;
	public string $sellingNetwork;
}
