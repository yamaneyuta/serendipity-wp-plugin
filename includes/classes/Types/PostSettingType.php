<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Types;

class PostSettingType {
	public function __construct( PriceType $price, string $selling_network ) {
		$this->sellingPrice = $price;
		$this->sellingNetwork = $selling_network;
	}

	// プロパティはGraphQLで使用するためcamelCase

	public PriceType $sellingPrice;
	public string $sellingNetwork;
}
