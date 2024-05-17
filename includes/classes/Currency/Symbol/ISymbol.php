<?php

declare(strict_types=1);

namespace Cornix\Serendipity\Core\Currency\Symbol;

use Cornix\Serendipity\Core\Web3\IContract;

interface ISymbol {
	public function getDecimals( string $symbol ): int;
}
