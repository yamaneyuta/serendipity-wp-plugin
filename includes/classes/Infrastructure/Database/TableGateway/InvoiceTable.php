<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\TableGateway;

use Cornix\Serendipity\Core\Entity\Invoice;
use Cornix\Serendipity\Core\Repository\Name\TableName;
use Cornix\Serendipity\Core\ValueObject\InvoiceID;
use Cornix\Serendipity\Core\Infrastructure\Database\ValueObject\InvoiceTableRecord;

/**
 * 発行した請求書の情報を保存するテーブル
 */
class InvoiceTable extends TableBase {
	public function __construct( \wpdb $wpdb ) {
		parent::__construct( $wpdb, ( new TableName() )->invoice() );
	}

	/**
	 * @inheritdoc
	 * 購入用請求書テーブルを作成します。
	 *
	 * ■nonce列
	 *
	 * 【目的】
	 * 購入者が購入手続きを完了し、記事の続きを取得する際にウォレットの署名を省略するためにnonceを使用する。
	 *
	 * 【背景】
	 * ウォレット確認は署名を行うことで実現可能だが、ユーザーのアクションが必要なため省略したい。
	 * 基本的に購入者は支払い手続きと記事の続き取得を同じブラウザで行うため、
	 * 請求書ID発行時にnonceも保存しておくことで、対象の請求書ID発行元であることが確認でき、ウォレットによる署名操作を省略できる。
	 *
	 * 【運用】
	 * 1. サーバー: 請求書ID発行時にnonceを生成し、このテーブルに保存する。
	 * 2. クライアント: 記事の続き取得時など、ウォレットの所有者のみ取得可能な情報は、請求書IDとnonceをサーバーへ送信する。
	 * 3. サーバー: 請求書IDとnonceの組み合わせを受信した場合は値を検証し、問題がなければ新しいnonceを発行後、このテーブルに保存する。
	 *              生成した新しいnonceをAPIの応答の一部として返す。
	 * 4. クライアント: 新しいnonceを受信した場合は、ブラウザに保存する。
	 * 5. クライアント: 次回アクセス時は`2.`から再開
	 */
	public function create(): void {
		$charset    = $this->wpdb()->get_charset_collate();
		$index_name = "idx_{$this->tableName()}_2D6F4376";

		// - 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		$sql = <<<SQL
			CREATE TABLE `{$this->tableName()}` (
				`created_at`             timestamp               NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`id`                     varchar(191)            NOT NULL,
				`post_id`			     bigint        unsigned  NOT NULL,
				`chain_id`               bigint        unsigned  NOT NULL,
				`selling_amount_hex`     varchar(191)            NOT NULL,
				`selling_decimals`       int                     NOT NULL,
				`selling_symbol`         varchar(191)            NOT NULL,
				`seller_address`         varchar(191)            NOT NULL,
				`payment_token_address`  varchar(191)            NOT NULL,
				`payment_amount_hex`     varchar(191)            NOT NULL,
				`consumer_address`       varchar(191)            NOT NULL,
				`nonce`                  varchar(191) 		     DEFAULT NULL,
				PRIMARY KEY (`id`),
				KEY `{$index_name}` (`created_at`)
			) {$charset};
		SQL;

		$result = $this->mysqli()->query( $sql );
		if ( true !== $result ) {
			throw new \RuntimeException( '[BAC2FC48] Failed to create invoice table. ' . $this->mysqli()->error );
		}
	}

	/**
	 *
	 * @param InvoiceID $invoice_ID
	 * @return null|InvoiceTableRecord
	 */
	public function select( InvoiceID $invoice_ID ) {
		$sql = <<<SQL
			SELECT
				`id`,
				`post_id`,
				`chain_id`,
				`selling_amount_hex`,
				`selling_decimals`,
				`selling_symbol`,
				`seller_address`,
				`payment_token_address`,
				`payment_amount_hex`,
				`consumer_address`,
				`nonce`
			FROM `{$this->tableName()}`
			WHERE `id` = %s
		SQL;

		$sql = $this->wpdb()->prepare( $sql, $invoice_ID->ulid() );

		$record = $this->wpdb()->get_row( $sql );
		if ( null !== $record ) {
			$record->post_id          = (int) $record->post_id;
			$record->chain_id         = (int) $record->chain_id;
			$record->selling_decimals = (int) $record->selling_decimals;
		}

		return is_null( $record ) ? null : new InvoiceTableRecord( $record );
	}

	public function insert( Invoice $invoice ): void {
		$result = $this->wpdb()->insert(
			$this->tableName(),
			array(
				'id'                    => $invoice->id()->ulid(),
				'post_id'               => $invoice->postID(),
				'chain_id'              => $invoice->chainID(),
				'selling_amount_hex'    => $invoice->sellingPrice()->amountHex(),
				'selling_decimals'      => $invoice->sellingPrice()->decimals(),
				'selling_symbol'        => $invoice->sellingPrice()->symbol(),
				'seller_address'        => $invoice->sellerAddress()->value(),
				'payment_token_address' => $invoice->paymentTokenAddress()->value(),
				'payment_amount_hex'    => $invoice->paymentAmountHex(),
				'consumer_address'      => $invoice->consumerAddress()->value(),
				'nonce'                 => $invoice->nonce() ? $invoice->nonce()->value() : null,
			),
		);
		if ( false === $result || $this->wpdb()->last_error ) {
			throw new \RuntimeException( '[5F99E86E] Failed to insert invoice. ' . $this->wpdb()->last_error );
		}
	}
}
