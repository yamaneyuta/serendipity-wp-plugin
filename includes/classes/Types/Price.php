<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Types;

class Price {
	public function __construct( $amountHex, $decimals, $symbol ) {
		$this->amountHex = $amountHex;
		$this->decimals  = $decimals;
		$this->symbol    = $symbol;
	}

	/** @var string */
	public $amountHex;

	/** @var int */
	public $decimals;

	/** @var string */
	public $symbol;
}
