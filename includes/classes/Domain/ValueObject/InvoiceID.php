<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

use Cornix\Serendipity\Core\Lib\Calc\Hex;
use phpseclib\Math\BigInteger;
use yamaneyuta\Ulid;

/**
 * 請求書IDを表すクラス
 */
class InvoiceID {

	private function __construct( Ulid $ulid ) {
		$this->ulid = $ulid;
	}
	private Ulid $ulid;

	/**
	 * 文字列(HEX/ULID)の請求書IDをオブジェクトに変換します。
	 *
	 * @param string|BigInteger $invoice_ID_val 請求書ID
	 */
	public static function from( $invoice_ID_val ): InvoiceID {
		if ( is_string( $invoice_ID_val ) ) {
			return new InvoiceID( Ulid::from( $invoice_ID_val ) );
		} elseif ( $invoice_ID_val instanceof BigInteger ) {
			return new InvoiceID( Ulid::from( Hex::from( $invoice_ID_val ) ) );
		}
		throw new \InvalidArgumentException( '[DEE2905B] Invalid invoice ID. ' . var_export( $invoice_ID_val, true ) );
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
