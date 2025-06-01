<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Entity;

class AppContract {
	public function __construct( int $chain_id, string $address ) {
		$this->chain_id = $chain_id;
		$this->address  = $address;
	}

	public int $chain_id;
	public string $address;
}
