<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\ValueObject;

/**
 * nonceを表す基底クラス
 */
abstract class NonceBase {

	protected function __construct( string $nonce_value ) {
		if ( ! $this->isNonceValueFormat( $nonce_value ) ) {
			throw new \InvalidArgumentException( "[6A2C68E6] Invalid invoice nonce value format: {$nonce_value}" );
		}
		$this->nonce_value = $nonce_value;
	}

	private string $nonce_value;

	/** nonceの文字列を取得します */
	public function value(): string {
		return $this->nonce_value;
	}

	/** Nonceのフォーマットが正しいかどうかを返します */
	abstract protected function isNonceValueFormat( string $nonce_value ): bool;

	/**
	 * 指定したバイト長のnonce値を生成します。
	 */
	protected static function generateNonceValue( int $byte ): string {
		// `wp_generate_uuid4`は`mt_rand`を用いているため、別の方法で乱数を生成する。
		// 参考:
		// - wp_generate_uuid4: https://developer.wordpress.org/reference/functions/wp_generate_uuid4/
		// - mt_rand: https://www.php.net/manual/ja/function.mt-rand.php
		// 　> この関数が生成する値は、暗号学的にセキュアではありません。そのため、これを暗号や、戻り値を推測できないことが必須の値として使っては いけません。
		// 　> 簡単なユースケースの場合、random_int() と random_bytes() 関数が、オペレーティングシステムの CSPRNG を使った、 便利で安全な API を提供します。

		$nonce = random_bytes( $byte );
		return bin2hex( $nonce );
	}
}
