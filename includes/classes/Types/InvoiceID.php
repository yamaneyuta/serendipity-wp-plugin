<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Types;

use yamaneyuta\Ulid;

/**
 * 請求書ID
 */
class InvoiceID {

	private function __construct( Ulid $ulid ) {
		$this->ulid = $ulid;
	}
	private Ulid $ulid;

	/**
	 * 新しい請求書IDを生成します。
	 */
	public static function generate(): InvoiceID {
		return new InvoiceID( new Ulid() );
	}

	/**
	 * `0x`プレフィックスを含むhex形式で値を取得します。
	 */
	public function hex(): string {
		return '0x' . $this->ulid->toHex();
	}

	/**
	 * UUID形式で値を取得します。
	 */
	public function uuid(): string {
		return $this->ulid->toUuid();
	}
}
