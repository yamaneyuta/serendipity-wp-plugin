<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Database\Table;

use Cornix\Serendipity\Core\Lib\Database\MySQLiFactory;
use Cornix\Serendipity\Core\Repository\Name\TableName;
use Cornix\Serendipity\Core\ValueObject\InvoiceID;
use Cornix\Serendipity\Core\ValueObject\InvoiceNonce;

/**
 * 発行した請求書IDとnonceの紐づきを保存するテーブル
 *
 * 【目的】
 * 購入者が購入手続きを完了し、記事の続きを取得する際にウォレットの署名を省略するためにnonceを使用する。
 *
 * 【背景】
 * ほぼすべての購入者は支払い手続きと記事の続き取得を同じブラウザで行うため、
 * 請求書ID発行時にnonceも保存しておくことで、対象の請求書ID発行元であることが確認できる。
 *
 * 【運用】
 * 1.   サーバー: 請求書ID発行時にnonceを生成し、このテーブルに保存する。
 * 2.   クライアント: トランザクションの残り待機ブロック数取得や記事の続き取得時には、請求書IDとnonceを送信する。
 * 3.1. サーバー: 残り待機ブロック数取得時は請求書IDとnonceの組み合わせを検証し、問題がなければ新しいnonceを発行し、このテーブルに保存する。
 * 　             その新しいnonceをAPIの応答の一部として返す。
 * 3.2. サーバー: 記事の続き取得時は請求書IDとnonceの組み合わせを検証し、問題がなければ記事の続きを返し、このテーブルのレコードを削除する。
 * 4.1. クライアント: 残り待機ブロック数と新しいnonceを受信した場合は、新しいnonceを保存する。
 * 4.2. クライアント: 記事の続きを受信した場合は保存していたnonceを削除する(以後使用できなくなるため)。
 */
class InvoiceNonceTable {
	public function __construct( \wpdb $wpdb = null ) {
		$this->wpdb       = $wpdb ?? $GLOBALS['wpdb'];
		$this->table_name = ( new TableName() )->invoiceNonce();
	}

	private \wpdb $wpdb;
	private string $table_name;

	private function mysqli(): \mysqli {
		return ( new MySQLiFactory() )->create( $this->wpdb );
	}

	/**
	 * 請求書IDに対応するnonceを保存するテーブルを作成します。
	 */
	public function create(): void {
		$charset = $this->wpdb->get_charset_collate();

		// - 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		$sql = <<<SQL
			CREATE TABLE `{$this->table_name}` (
				`created_at`  timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`updated_at`  timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`invoice_id`  varchar(191)  NOT NULL,
				`nonce`       varchar(191)  NOT NULL,
				PRIMARY KEY (`invoice_id`)
			) {$charset};
		SQL;

		$mysqli = $this->mysqli();
		$result = $mysqli->query( $sql );
		if ( true !== $result ) {
			throw new \RuntimeException( '[A1CB0FAD] Failed to create invoice nonce table. ' . $mysqli->error );
		}
	}

	/** 指定した請求書IDに紐づくnonceを記録します。 */
	public function set( InvoiceID $invoice_ID, InvoiceNonce $nonce ): void {
		$sql = <<<SQL
			INSERT INTO `{$this->table_name}`
			(`invoice_id`, `nonce`)
			VALUES (%s, %s)
			ON DUPLICATE KEY UPDATE `nonce` = VALUES(`nonce`);
		SQL;

		$sql = $this->wpdb->prepare( $sql, $invoice_ID->ulid(), $nonce->value() );

		$result = $this->wpdb->query( $sql );
		if ( false === $result ) {
			throw new \RuntimeException( '[9A316865] Failed to set invoice nonce. ' . $this->wpdb->last_error );
		}
	}

	/**
	 * 指定した請求書IDに紐づくnonceを取得します。
	 */
	public function getNonce( InvoiceID $invoice_ID ): ?InvoiceNonce {
		$sql = <<<SQL
			SELECT `nonce`
			FROM `{$this->table_name}`
			WHERE `invoice_id` = %s
		SQL;

		$sql = $this->wpdb->prepare( $sql, $invoice_ID->ulid() );

		$nonce = $this->wpdb->get_var( $sql );
		if ( is_null( $nonce ) ) {
			return null;
		}

		return new InvoiceNonce( $nonce );
	}

	/**
	 * 請求書IDに対応するnonceを保存するテーブルを削除します。
	 */
	public function drop(): void {
		$sql = <<<SQL
			DROP TABLE IF EXISTS `{$this->table_name}`;
		SQL;

		$mysqli = $this->mysqli();
		$result = $mysqli->query( $sql );
		if ( true !== $result ) {
			throw new \RuntimeException( '[9D11FBD7] Failed to drop invoice nonce table. ' . $mysqli->error );
		}
	}
}
