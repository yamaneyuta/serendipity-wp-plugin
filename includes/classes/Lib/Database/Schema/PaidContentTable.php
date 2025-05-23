<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Database\Schema;

use Cornix\Serendipity\Core\Lib\Database\MySQLiFactory;
use Cornix\Serendipity\Core\Lib\Repository\Name\TableName;

/**
 * 有料記事の情報を記録するテーブル
 */
class PaidContentTable {

	public function __construct( \wpdb $wpdb = null ) {
		$this->wpdb       = $wpdb ?? $GLOBALS['wpdb'];
		$this->mysqli     = ( new MySQLiFactory() )->create( $this->wpdb );
		$this->table_name = ( new TableName() )->paidContent();
	}

	private \wpdb $wpdb;
	private \mysqli $mysqli;
	private string $table_name;

	/**
	 * テーブルを作成します。
	 */
	public function create(): void {
		// リビジョンも含めてレコードが生成されます。
		// 　- 現在の投稿ID -> レコードの上書きあり
		// 　- リビジョンの投稿ID -> レコードの上書きなし
		// 投稿が削除された場合や、リビジョンが削除された場合は
		// このテーブルからも削除されます。(Hooksディレクトリ内を参照)

		$charset = $this->wpdb->get_charset_collate();
		$sql     = <<<SQL
			CREATE TABLE `{$this->table_name}` (
				`created_at`                   timestamp            NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`updated_at`                   timestamp            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`post_id`                      bigint     unsigned  NOT NULL,
				`paid_content`                 longtext             NOT NULL,
				`selling_network_category_id`  int                  NOT NULL,
				`selling_amount_hex`           varchar(191)         NOT NULL,
				`selling_decimals`             int                  NOT NULL,
				`selling_symbol`               varchar(191)         NOT NULL,
				PRIMARY KEY (`post_id`)
			) {$charset};
		SQL;

		$result = $this->mysqli->query( $sql );
		assert( true === $result );
	}

	/**
	 * テーブルを削除します。
	 */
	public function drop(): void {
		$sql = <<<SQL
			DROP TABLE IF EXISTS `{$this->table_name}`;
		SQL;

		$result = $this->mysqli->query( $sql );
		assert( true === $result );
	}
}
