<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

class Terms {
	/**
	 * @param TermsVersion   $version 利用規約のバージョン
	 * @param SigningMessage $message 利用規約署名用メッセージ
	 */
	public function __construct( TermsVersion $version, SigningMessage $message ) {
		$this->version = $version;
		$this->message = $message;
	}

	private TermsVersion $version;
	private SigningMessage $message;

	public function version(): TermsVersion {
		return $this->version;
	}

	public function message(): SigningMessage {
		return $this->message;
	}
}
