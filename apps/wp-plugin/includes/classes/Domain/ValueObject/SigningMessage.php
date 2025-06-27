<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

/** 署名のためのメッセージ */
class SigningMessage {

	public function __construct( string $signing_message_value ) {
		$this->signing_message_value = $signing_message_value;
	}

	private string $signing_message_value;

	public function value(): string {
		return $this->signing_message_value;
	}

	public function __toString(): string {
		return $this->signing_message_value;
	}
}
