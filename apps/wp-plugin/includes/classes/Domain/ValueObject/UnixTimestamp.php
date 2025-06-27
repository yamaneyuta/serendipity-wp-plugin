<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

use DateTimeImmutable;

class UnixTimestamp {
	public function __construct( int $timestamp ) {
		$this->timestamp = $timestamp;
	}

	private int $timestamp;

	public function value(): int {
		return $this->timestamp;
	}

	public static function now(): self {
		return new self( time() );
	}

	public function __toString(): string {
		return ( new DateTimeImmutable() )->setTimestamp( $this->timestamp )->format( 'Y-m-d H:i:s' );
	}
}
