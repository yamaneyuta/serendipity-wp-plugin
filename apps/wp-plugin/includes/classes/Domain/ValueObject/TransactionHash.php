<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

class TransactionHash {
	public function __construct( string $hash ) {
		$this->hash = $hash;
	}

	private string $hash;

	/**
	 * トランザクションハッシュを取得します。
	 */
	public function value(): string {
		return $this->hash;
	}

	public static function from( string $hash ): self {
		// フォーマットチェック
		if ( ! preg_match( '/^0x[a-fA-F0-9]{64}$/', $hash ) ) {
			throw new \InvalidArgumentException( '[7AF48A4D] Invalid transaction hash format: ' . $hash );
		}

		return new self( $hash );
	}

	/**
	 * トランザクションハッシュを文字列として返します。
	 */
	public function __toString(): string {
		return $this->hash;
	}
}
