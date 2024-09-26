<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Database\Schema;

use Cornix\Serendipity\Core\Lib\Database\MySQLiFactory;
use Cornix\Serendipity\Core\Lib\Repository\Database\TableName;

// 他のテーブルと結合するための価格パターンを格納するテーブル
//
//
// 用途: 購入時のブロックチェーントランザクションと購入時の設定価格の紐づけ
// 　    ⇒ 購入用チケット発行時にこのテーブルに販売価格を登録しておき、
// 　       ブロックチェーントランザクションにあるチケットIDから販売価格を取得する

class PricePatternTable {
	public function __construct( \wpdb $wpdb ) {
		$this->wpdb       = $wpdb;
		$this->mysqli     = ( new MySQLiFactory() )->create( $wpdb );
		$this->table_name = ( new TableName() )->pricePattern();
	}

	private \wpdb $wpdb;
	private \mysqli $mysqli;
	private string $table_name;

	/**
	 * 価格パターンテーブルを作成します。
	 */
	public function create(): void {
		$charset = $this->wpdb->get_charset_collate();

		// - 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		$sql = <<<SQL
			CREATE TABLE `{$this->table_name}` (
				`id`                   varchar(191)        NOT NULL,
				`amount_hex`           varchar(191)        NOT NULL,
				`decimals`             int                 NOT NULL,
				`symbol`               varchar(191)        NOT NULL,
				PRIMARY KEY (`id`)
			) ${charset};
		SQL;

		$result = $this->mysqli->query( $sql );
		assert( true === $result );
	}

	/**
	 * 価格パターンテーブルを削除します。
	 */
	public function drop(): void {
		$sql = <<<SQL
			DROP TABLE IF EXISTS `{$this->table_name}`;
		SQL;

		$result = $this->mysqli->query( $sql );
		assert( true === $result );
	}
}
