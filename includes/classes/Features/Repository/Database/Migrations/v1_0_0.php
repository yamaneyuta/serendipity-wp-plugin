<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\Repository\Database\Migrations;

use Cornix\Serendipity\Core\Lib\Repository\Database\TableName;
use wpdb;

class v1_0_0 extends MigrationBase {

	public function __construct( wpdb $wpdb ) {
		parent::__construct( $wpdb );
	}

	/**
	 * @inheritDoc
	 */
	// #[\Override]
	public function up() {
		$table_name = TableName::postSettingHistory();
		$charset    = $this->charset();

		// 投稿IDに紐づく情報を記録するテーブル。
		// 同一`post_id`のレコードが複数存在するので、現在の設定を取得する場合は`id`が最大のレコードを取得する。
		//
		// - 一つでも設定を変更した場合、新しいレコードが作成されるが、
		// 投稿を作成することができるユーザーによる画面操作のため、何十万レコードも作成されることは一般的にないと考えられる。
		// そのため、正規化は行わない。
		// - 複数回呼び出された時に検知できるように`IF NOT EXISTS`は使用しない
		$sql = <<<SQL
			CREATE TABLE `${table_name}` (
				`created_at`                   datetime            NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`updated_at`                   datetime            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`id`                           varchar(191)        NOT NULL,
				`post_id`                      bigint(20) unsigned NOT NULL,
				`selling_amount_hex`           varchar(191)        NOT NULL,
				`selling_decimals`             int                 NOT NULL,
				`selling_symbol`               varchar(191)        NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=${charset};
		SQL;
		// -- `selling_paused`               tinyint(1)          NOT NULL,
		// -- `affiliate_percent_amount_hex` varchar(191)        NOT NULL,
		// -- `affiliate_percent_decimals`   int                 NOT NULL,

		$result = $this->mysqli->query( $sql );
		assert( true === $result );
	}

	/**
	 * @inheritDoc
	 */
	// #[\Override]
	public function down() {
		$table_name = TableName::postSettingHistory();

		// ここはupの途中で失敗してロールバックが呼ばれる可能性があるため、`IF EXISTS`を使用する。
		$sql = <<<SQL
			DROP TABLE IF EXISTS `${table_name}`;
		SQL;

		$this->mysqli->query( $sql );
	}
}
