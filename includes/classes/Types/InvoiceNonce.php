<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Types;

use Cornix\Serendipity\Core\Lib\Security\Judge;

/**
 * 請求書に紐づくnonceを表すクラス
 *
 * 請求書を発行したクライアントを識別するために使用されます。
 */
class InvoiceNonce {
	/**
	 * 請求書に紐づくnonceインスタンスを生成します。
	 * コンストラクタの引数に値を指定しない場合は、ランダムなnonceが生成されます。
	 */
	public function __construct( string $invoice_nonce_value = null ) {
		$invoice_nonce_value = $invoice_nonce_value ?? self::generateNonceValue();
		assert(
			Judge::isInvoiceNonceValueFormat( $invoice_nonce_value ),
			'[6A2C68E6] Invalid invoice nonce value format: ' . var_export( $invoice_nonce_value, true )
		);
		$this->value = $invoice_nonce_value;
	}

	private string $value;

	public function value(): string {
		return $this->value;
	}

	/**
	 * nonceを生成します。
	 */
	private static function generateNonceValue(): string {
		// `wp_generate_uuid4`は`mt_rand`を用いているため、別の方法で乱数を生成する。
		// 参考:
		// - wp_generate_uuid4: https://developer.wordpress.org/reference/functions/wp_generate_uuid4/
		// - mt_rand: https://www.php.net/manual/ja/function.mt-rand.php
		// 　> この関数が生成する値は、暗号学的にセキュアではありません。そのため、これを暗号や、戻り値を推測できないことが必須の値として使っては いけません。
		// 　> 簡単なユースケースの場合、random_int() と random_bytes() 関数が、オペレーティングシステムの CSPRNG を使った、 便利で安全な API を提供します。

		$nonce = random_bytes( 16 ); // UUIDv4と同じ長さ(128bit)で生成
		return bin2hex( $nonce );
	}
}
