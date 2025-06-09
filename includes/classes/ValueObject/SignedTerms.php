<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\ValueObject;

/** 署名済み利用規約情報 */
class SignedTerms {
	public function __construct( Terms $terms, string $signature ) {
		$this->terms     = $terms;
		$this->signature = $signature;
	}

	private Terms $terms;
	private string $signature;

	public function terms(): Terms {
		return $this->terms;
	}

	public function signature(): string {
		return $this->signature;
	}
}
