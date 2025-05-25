<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Database\Schema;

use Cornix\Serendipity\Core\Lib\Database\MySQLiFactory;
use Cornix\Serendipity\Core\Lib\Repository\Name\TableName;
use Cornix\Serendipity\Core\Types\Price;

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

	public function set( int $post_id, string $paid_content, int $selling_network_category_id, Price $selling_price ): void {
		$sql = <<<SQL
			INSERT INTO `{$this->table_name}` (
				`post_id`,
				`paid_content`,
				`selling_network_category_id`,
				`selling_amount_hex`,
				`selling_decimals`,
				`selling_symbol`
			) VALUES (
				%d, %s, %d, %s, %d, %s
			) ON DUPLICATE KEY UPDATE
				`paid_content` = %s,
				`selling_network_category_id` = %d,
				`selling_amount_hex` = %s,
				`selling_decimals` = %d,
				`selling_symbol` = %s
		SQL;

		$sql = $this->wpdb->prepare(
			$sql,
			array(
				$post_id,
				$paid_content,
				$selling_network_category_id,
				$selling_price->amountHex(),
				$selling_price->decimals(),
				$selling_price->symbol(),
				$paid_content,
				$selling_network_category_id,
				$selling_price->amountHex(),
				$selling_price->decimals(),
				$selling_price->symbol(),
			)
		);

		$result = $this->wpdb->query( $sql );

		if ( false === $result ) {
			throw new \Exception( '[8DAB2BCF] Failed to set paid content data.' );
		}
		assert( $result <= 1, "[DBB26475] Failed to set paid content data. - post_id: {$post_id}, result: {$result}" );
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
