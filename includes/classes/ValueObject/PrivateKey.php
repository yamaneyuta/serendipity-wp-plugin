<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\ValueObject;

class PrivateKey {

	private string $private_key_value;

	/** @disregard P1009 Undefined type */
	private function __construct(
		#[\SensitiveParameter]
		string $private_key_value
	) {
		// フォーマットチェック(最大64文字の16進数)
		if ( ! preg_match( '/^[a-f0-9]{1,64}$/', $private_key_value ) ) {
			throw new \InvalidArgumentException( '[5CE68177] Invalid private key format: ' . $private_key_value );
		}
		$this->private_key_value = $private_key_value;
	}

	/**
	 * プライベートキーを16進数の文字列(`0x`無し)で取得します。
	 */
	public function value(): string {
		return $this->private_key_value;
	}

	/** @disregard P1009 Undefined type */
	public static function from(
		#[\SensitiveParameter]
		string $private_key_value
	): self {
		return new self( $private_key_value );
	}

	/**
	 * プライベートキーを文字列として返します。
	 */
	public function __toString(): string {
		return $this->private_key_value;
	}

	public function __debugInfo() {
		return array(
			// プライベートキーはデバッグ出力から除外
			'private_key_value' => '*** sensitive data***',
		);
	}
}
