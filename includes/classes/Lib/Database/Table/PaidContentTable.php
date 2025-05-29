<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Database\Table;

use Cornix\Serendipity\Core\Lib\Database\MySQLiFactory;
use Cornix\Serendipity\Core\Repository\Name\TableName;
use Cornix\Serendipity\Core\Types\NetworkCategory;
use Cornix\Serendipity\Core\ValueObject\Price;

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
	 * @return null|object{
	 *   created_at: string,
	 *   updated_at: string,
	 *   post_id: int,
	 *   paid_content: string,
	 *   selling_network_category_id: int,
	 *   selling_amount_hex: string,
	 *   selling_decimals: int,
	 *   selling_symbol: string
	 * }
	 */
	public function select( int $post_id ) {
		$sql = <<<SQL
			SELECT *
			FROM `{$this->table_name}`
			WHERE `post_id` = %d
		SQL;

		$sql    = $this->wpdb->prepare( $sql, $post_id );
		$result = $this->wpdb->get_row( $sql );

		if ( ! is_null( $result ) ) {
			$result->post_id                     = (int) $result->post_id;
			$result->selling_network_category_id = (int) $result->selling_network_category_id;
			$result->selling_decimals            = (int) $result->selling_decimals;
		}

		return $result;
	}

	public function set( int $post_id, string $paid_content, ?NetworkCategory $selling_network_category, ?Price $selling_price ): void {
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

		$selling_network_category_id = is_null( $selling_network_category ) ? null : $selling_network_category->id();
		$selling_price_amount_hex    = is_null( $selling_price ) ? null : $selling_price->amountHex();
		$selling_price_decimals      = is_null( $selling_price ) ? null : $selling_price->decimals();
		$selling_price_symbol        = is_null( $selling_price ) ? null : $selling_price->symbol();

		$sql = $this->wpdb->prepare(
			$sql,
			array(
				$post_id,
				$paid_content,
				$selling_network_category_id,
				$selling_price_amount_hex,
				$selling_price_decimals,
				$selling_price_symbol,
				$paid_content,
				$selling_network_category_id,
				$selling_price_amount_hex,
				$selling_price_decimals,
				$selling_price_symbol,
			)
		);

		$result = $this->wpdb->query( $sql );

		if ( false === $result ) {
			throw new \Exception( '[8DAB2BCF] Failed to set paid content data.' );
		}
		assert( $result <= 2, "[DBB26475] Failed to set paid content data. - post_id: {$post_id}, result: {$result}" );
	}

	public function delete( int $post_id ): void {
		$sql = <<<SQL
			DELETE FROM `{$this->table_name}` WHERE `post_id` = %d
		SQL;

		$sql    = $this->wpdb->prepare( $sql, $post_id );
		$result = $this->wpdb->query( $sql );

		if ( false === $result ) {
			throw new \Exception( '[C40F74D9] Failed to delete paid content data.' );
		}
		assert( $result <= 1, "[64CF23D9] Failed to delete paid content data. - post_id: {$post_id}, result: {$result}" );
	}

	/**
	 * テーブルが存在するかどうかを取得します。
	 */
	public function exists(): bool {
		return (bool) $this->wpdb->get_var( "SHOW TABLES LIKE '{$this->table_name}'" );
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
