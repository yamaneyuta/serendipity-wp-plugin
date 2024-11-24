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
	 * 文字列(HEX/ULID)の請求書IDをオブジェクトに変換します。
	 */
	public static function from( string $invoice_ID ): InvoiceID {
		return new InvoiceID( Ulid::from( $invoice_ID ) );
	}

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
	 * ULID形式で値を取得します。
	 * ※ 基本的にDBに保存する際に使用します。
	 */
	public function ulid(): string {
		return $this->ulid->toString();
	}
}
