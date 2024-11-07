<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Name\TableName;
use Cornix\Serendipity\Core\Types\InvoiceID;

class InvoiceNonce {
	public function __construct( \wpdb $wpdb ) {
		$this->wpdb       = $wpdb;
		$this->table_name = ( new TableName() )->invoiceNonce();
	}

	private \wpdb $wpdb;
	private string $table_name;

	/**
	 * nonceを生成します。
	 */
	private function generateNonce(): string {
		// `wp_generate_uuid4`は`mt_rand`を用いているため、別の方法で乱数を生成する。
		// 参考:
		// - wp_generate_uuid4: https://developer.wordpress.org/reference/functions/wp_generate_uuid4/
		// - mt_rand: https://www.php.net/manual/ja/function.mt-rand.php
		// 　> この関数が生成する値は、暗号学的にセキュアではありません。そのため、これを暗号や、戻り値を推測できないことが必須の値として使っては いけません。
		// 　> 簡単なユースケースの場合、random_int() と random_bytes() 関数が、オペレーティングシステムの CSPRNG を使った、 便利で安全な API を提供します。

		$nonce = random_bytes( 16 ); // UUIDv4と同じ長さ(128bit)で生成
		return bin2hex( $nonce );
	}

	/**
	 * 請求書IDに対応するnonceを新しく生成し、保存します。
	 */
	public function new( InvoiceID $invoice_ID ): string {
		$nonce = $this->generateNonce(); // 新しいnonceを生成

		$sql = <<<SQL
			INSERT INTO `{$this->table_name}`
			(`invoice_id_hex`, `nonce`)
			VALUES (%s, %s)
		SQL;

		$sql = $this->wpdb->prepare( $sql, $invoice_ID->hex(), $nonce );

		$result = $this->wpdb->query( $sql );
		assert( false !== $result );

		return $nonce;
	}
}
