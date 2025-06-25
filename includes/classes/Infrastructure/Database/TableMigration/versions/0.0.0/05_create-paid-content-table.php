<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\TableMigration\versions\_0_0_1;

use Cornix\Serendipity\Core\Infrastructure\Database\TableMigration\DatabaseMigrationBase;
use Cornix\Serendipity\Core\Repository\Name\TableName;

return new class() extends DatabaseMigrationBase {

	public function __construct() {
		$this->table_name = ( new TableName() )->paidContent();
	}
	private string $table_name;

	/** @inheritdoc */
	public function up(): void {
		// リビジョンも含めてレコードが生成されます。
		// 　- 現在の投稿ID -> レコードの上書きあり
		// 　- リビジョンの投稿ID -> レコードの上書きなし
		// 投稿が削除された場合や、リビジョンが削除された場合は
		// このテーブルからも削除されます。(Hooksディレクトリ内を参照)

		$charset = $this->wpdb()->get_charset_collate();
		$sql     = <<<SQL
			CREATE TABLE `{$this->table_name}` (
				`created_at`                   timestamp            NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`updated_at`                   timestamp            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`post_id`                      bigint     unsigned  NOT NULL,
				`paid_content`                 longtext             NOT NULL,
				`selling_network_category_id`  int,
				`selling_amount`               varchar(191),
				`selling_symbol`               varchar(191),
				PRIMARY KEY (`post_id`)
			) {$charset};
		SQL;

		$result = $this->mysqli()->query( $sql );
		if ( true !== $result ) {
			throw new \RuntimeException( '[6B57C683] Error: ' . $this->mysqli()->error );
		}
	}

	/** @inheritdoc */
	public function down(): void {
		$sql    = "DROP TABLE IF EXISTS `{$this->table_name}`;";
		$result = $this->mysqli()->query( $sql );
		if ( true !== $result ) {
			throw new \RuntimeException( '[AD9FA9F2] Error: ' . $this->mysqli()->error );
		}
	}
};
