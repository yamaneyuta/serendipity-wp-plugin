<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Database\Schema;

use Cornix\Serendipity\Core\Lib\Database\MySQLiFactory;
use Cornix\Serendipity\Core\Lib\Repository\Name\TableName;


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
	public function __construct( \wpdb $wpdb ) {
		$this->wpdb       = $wpdb;
		$this->mysqli     = ( new MySQLiFactory() )->create( $wpdb );
		$this->table_name = ( new TableName() )->invoiceNonce();
	}

	private \wpdb $wpdb;
	private \mysqli $mysqli;
	private string $table_name;

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
			) ${charset};
		SQL;

		$result = $this->mysqli->query( $sql );
		assert( true === $result );
	}

	/**
	 * 請求書IDに対応するnonceを保存するテーブルを削除します。
	 */
	public function drop(): void {
		$sql = <<<SQL
			DROP TABLE IF EXISTS `{$this->table_name}`;
		SQL;

		$result = $this->mysqli->query( $sql );
		assert( true === $result );
	}
}
