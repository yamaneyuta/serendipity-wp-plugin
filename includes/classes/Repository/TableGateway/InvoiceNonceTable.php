<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository\TableGateway;

use Cornix\Serendipity\Core\Repository\Name\TableName;
use Cornix\Serendipity\Core\ValueObject\InvoiceID;
use Cornix\Serendipity\Core\ValueObject\InvoiceNonce;
use Cornix\Serendipity\Core\ValueObject\TableRecord\InvoiceNonceTableRecord;

/**
 * 発行した請求書IDとnonceの紐づきを保存するテーブル
 *
 * 【目的】
 * 購入者が購入手続きを完了し、記事の続きを取得する際にウォレットの署名を省略するためにnonceを使用する。
 *
 * 【背景】
 * 基本的に購入者は支払い手続きと記事の続き取得を同じブラウザで行うため、
 * 請求書ID発行時にnonceも保存しておくことで、対象の請求書ID発行元であることが確認できる。
 *
 * 【運用】
 * 1. サーバー: 請求書ID発行時にnonceを生成し、このテーブルに保存する。
 * 2. クライアント: 記事の続き取得時など、ウォレットの所有者のみ取得可能な情報は、請求書IDとnonceをサーバーへ送信する。
 * 3. サーバー: 請求書IDとnonceの組み合わせを受信した場合は値を検証し、問題がなければ新しいnonceを発行後、このテーブルに保存する。
 *              生成した新しいnonceをAPIの応答の一部として返す。
 * 4. クライアント: 新しいnonceを受信した場合は、ブラウザに保存する。
 * 5. クライアント: 次回アクセス時は`2.`から再開
 */
class InvoiceNonceTable extends TableBase {
	public function __construct( \wpdb $wpdb ) {
		parent::__construct( $wpdb, ( new TableName() )->invoiceNonce() );
	}

	/**
	 * 請求書IDに対応するnonceを保存するテーブルを作成します。
	 */
	public function create(): void {
		$charset = $this->wpdb()->get_charset_collate();

		// - 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		$sql = <<<SQL
			CREATE TABLE `{$this->tableName()}` (
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

	public function select( InvoiceID $invoice_ID ): ?InvoiceNonceTableRecord {
		$sql = <<<SQL
			SELECT
				`invoice_id`,
				`nonce`
			FROM `{$this->tableName()}`
			WHERE `invoice_id` = %s
		SQL;

		$sql = $this->wpdb()->prepare( $sql, $invoice_ID->ulid() );

		$record = $this->wpdb()->get_row( $sql );

		return is_null( $record ) ? null : new InvoiceNonceTableRecord( $record );
	}

	/** 指定した請求書IDに紐づくnonceを記録します。 */
	public function setNonce( InvoiceID $invoice_ID, InvoiceNonce $nonce ): void {
		$sql = <<<SQL
			INSERT INTO `{$this->tableName()}`
			(`invoice_id`, `nonce`)
			VALUES (%s, %s)
			ON DUPLICATE KEY UPDATE `nonce` = VALUES(`nonce`);
		SQL;

		$sql = $this->wpdb()->prepare( $sql, $invoice_ID->ulid(), $nonce->value() );

		$result = $this->wpdb()->query( $sql );
		if ( false === $result ) {
			throw new \RuntimeException( '[9A316865] Failed to set invoice nonce. ' . $this->wpdb()->last_error );
		}
	}

	/**
	 * 指定した請求書IDに紐づくnonceを取得します。
	 */
	public function getNonce( InvoiceID $invoice_ID ): ?InvoiceNonce {
		$sql = <<<SQL
			SELECT `nonce`
			FROM `{$this->tableName()}`
			WHERE `invoice_id` = %s
		SQL;

		$sql = $this->wpdb()->prepare( $sql, $invoice_ID->ulid() );

		$nonce = $this->wpdb()->get_var( $sql );
		if ( is_null( $nonce ) ) {
			return null;
		}

		return new InvoiceNonce( $nonce );
	}
}
