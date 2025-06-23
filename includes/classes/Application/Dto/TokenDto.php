<?php

namespace Cornix\Serendipity\Core\Application\Dto;

use Cornix\Serendipity\Core\Domain\Entity\Token;

class TokenDto {

	private function __construct( int $chain_id, string $address ) {
		$this->chain_id = $chain_id;
		$this->address  = $address;
	}

	private int $chain_id;
	private string $address;

	public function chainId(): int {
		return $this->chain_id;
	}
	public function address(): string {
		return $this->address;
	}

	public static function fromEntity( Token $token ): self {
		return new self(
			$token->chainID()->value(),
			$token->address()->value(),
		);
	}
}
