<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

/**
 * 請求書に紐づくnonceを表すクラス
 *
 * 請求書を発行したクライアントを識別するために使用されます。
 */
final class InvoiceNonce extends NonceBase {

	private const NONCE_BYTES = 16; // 16byte(128bit)のnonceを生成する

	/**
	 * 請求書に紐づくnonceインスタンスを生成します。
	 */
	public function __construct( string $invoice_nonce_value ) {
		parent::__construct( $invoice_nonce_value );
	}

	/** @inheritdoc */
	protected function isNonceValueFormat( string $nonce_value ): bool {
		return 1 === preg_match( '/^[0-9a-f]{32}$/i', $nonce_value );
	}

	public static function generate(): self {
		return new self( self::generateNonceValue( self::NONCE_BYTES ) );
	}
}
