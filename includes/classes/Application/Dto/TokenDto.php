<?php

namespace Cornix\Serendipity\Core\Application\Dto;

use Cornix\Serendipity\Core\Domain\Entity\Token;

class TokenDto {

	private function __construct( string $address ) {
		$this->address = $address;
	}

	private string $address;
	public function address(): string {
		return $this->address;
	}

	public static function fromEntity( Token $token ): self {
		return new self(
			$token->address()->value(),
		);
	}
}
