<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

/** 署名済み利用規約情報 */
class SignedTerms {
	// TODO: string $signature => Signature $signature
	public function __construct( Terms $terms, string $signature ) {
		$this->terms     = $terms;
		$this->signature = new Signature( $signature );
	}

	private Terms $terms;
	private Signature $signature;

	public function terms(): Terms {
		return $this->terms;
	}

	public function signature(): Signature {
		return $this->signature;
	}
}
