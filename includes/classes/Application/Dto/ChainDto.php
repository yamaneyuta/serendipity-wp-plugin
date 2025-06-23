<?php

namespace Cornix\Serendipity\Core\Application\Dto;

use Cornix\Serendipity\Core\Domain\Entity\Chain;

class ChainDto {

	private function __construct( int $id, ?string $rpc_url, string $confirmations, int $network_category_id ) {
		$this->id                  = $id;
		$this->rpc_url             = $rpc_url;
		$this->confirmations       = $confirmations;
		$this->network_category_id = $network_category_id;
	}

	private int $id;
	private ?string $rpc_url;
	private string $confirmations;
	private int $network_category_id;

	public function id(): int {
		return $this->id;
	}
	public function rpcUrl(): ?string {
		return $this->rpc_url;
	}
	public function confirmations(): string {
		return $this->confirmations;
	}
	public function networkCategoryId(): int {
		return $this->network_category_id;
	}

	public static function fromEntity( Chain $chain ): self {
		return new self(
			$chain->id()->value(),
			$chain->rpcURL(),
			(string) $chain->confirmations(),
			$chain->networkCategoryID()->value(),
		);
	}
}
