<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Types;

class PostSettingType {
	public function __construct( PriceType $price ) {
		$this->price = $price;
	}

	public PriceType $price;
}
