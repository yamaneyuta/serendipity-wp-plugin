<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Types;

class PostSettingType {
	public function __construct( PriceType $price ) {
		$this->sellingPrice = $price;
	}

	public PriceType $sellingPrice; // GraphQLで使用するためcamelCase
}
