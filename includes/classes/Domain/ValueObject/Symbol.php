<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

/**
 * 通貨シンボルを表すValueObject
 */
final class Symbol {

	public function __construct( string $symbol_value ) {
		self::checkValidSymbolFormat( $symbol_value );
		$this->symbol_value = $symbol_value;
	}

	private string $symbol_value;

	public static function from( ?string $symbol_value ): ?self {
		return is_null( $symbol_value ) ? null : new self( $symbol_value );
	}

	public function value(): string {
		return $this->symbol_value;
	}

	public function equals( Symbol $other ): bool {
		return $this->symbol_value === $other->symbol_value;
	}

	public function __toString(): string {
		return $this->symbol_value;
	}

	private static function checkValidSymbolFormat( string $symbol_value ): void {
		// 様々な通貨記号が存在するため、空文字列以外であれば有効とする。
		if ( trim( $symbol_value ) !== $symbol_value || empty( $symbol_value ) ) {
			throw new \InvalidArgumentException( '[12CF7A2F] Invalid symbol format. ' . $symbol_value );
		}
	}
}
