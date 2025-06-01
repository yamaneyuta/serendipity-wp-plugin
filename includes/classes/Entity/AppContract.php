<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Entity;

class AppContract {
	public function __construct( Chain $chain, string $address ) {
		$this->chain   = $chain;
		$this->address = $address;
	}

	public Chain $chain;
	public string $address;
}
