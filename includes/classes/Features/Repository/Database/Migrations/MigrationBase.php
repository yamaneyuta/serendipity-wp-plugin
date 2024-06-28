<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\Repository\Database\Migrations;

use mysqli;
use wpdb;

abstract class MigrationBase {

	protected function __construct( wpdb $wpdb ) {
		$this->wpdb = $wpdb;
		// CREATE TABLE等をwpdb->queryで発行すると、`CREATE TEMPORARY TABLE`に置き換えられるため、mysqliを使用する。
		// ※ dbDelta()は接続先を切り替えることができないため、複数のMySQLに対してクエリが通るかどうかのテストができないため使用しない。
		// ※ `CRATE TABLE`を小文字で記述することで`TEMPORARY`が付与されることを回避できるが、仕様変更で動作しなくなる可能性があるため、mysqliを使用する。
		assert( $wpdb->is_mysql );
		$this->mysqli = $wpdb->dbh;// new mysqli( $wpdb->dbhost, $wpdb->dbuser, $wpdb->dbpassword, $wpdb->dbname );
	}

	private wpdb $wpdb;
	protected mysqli $mysqli;


	/**
	 * マイグレーションを実行します。
	 */
	abstract public function up();

	/**
	 * マイグレーションをロールバックします。
	 */
	abstract public function down();

	protected function charset(): string {
		$charset = 'utf8mb4';

		// WordPress4.2以降かつ、MySQL5.5以降の場合は基本的に`utf8mb4`が使用される。
		// https://www.ddwnet.com/2017/02/03/wordpress%E7%A7%BB%E8%A8%AD%E3%81%A7%E6%B3%A8%E6%84%8F%E3%81%99%E3%82%8Bdb%E6%96%87%E5%AD%97%E3%82%BB%E3%83%83%E3%83%88/
		assert( $this->wpdb->charset === $charset );

		return $charset;
	}
}
