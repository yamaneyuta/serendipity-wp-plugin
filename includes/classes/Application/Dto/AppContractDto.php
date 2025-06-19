<?php

namespace Cornix\Serendipity\Core\Application\Dto;

use Cornix\Serendipity\Core\Domain\Entity\AppContract;

class AppContractDto {

	private function __construct( string $address ) {
		$this->address = $address;
	}

	private int $address;

	public function address(): string {
		return $this->address;
	}
	public static function fromEntity( AppContract $app_contract ): self {
		return new self(
			$app_contract->address()->value()
		);
	}
}
