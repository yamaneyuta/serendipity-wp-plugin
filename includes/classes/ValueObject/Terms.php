<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\ValueObject;

class Terms {
	/**
	 * @param TermsVersion $version 利用規約のバージョン
	 * @param string       $message 利用規約署名用メッセージ
	 */
	public function __construct( TermsVersion $version, string $message ) {
		$this->version = $version;
		$this->message = $message;
	}

	private TermsVersion $version;
	private string $message;

	public function version(): TermsVersion {
		return $this->version;
	}

	public function message(): string {
		return $this->message;
	}
}
