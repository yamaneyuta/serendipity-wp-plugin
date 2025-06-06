<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\Database\TableGateway;

use Cornix\Serendipity\Core\Repository\Name\TableName;
use Cornix\Serendipity\Core\ValueObject\NetworkCategory;
use Cornix\Serendipity\Core\ValueObject\Price;
use Cornix\Serendipity\Core\Infrastructure\Database\ValueObject\PaidContentTableRecord;

/**
 * 有料記事の情報を記録するテーブル
 */
class PaidContentTable extends TableBase {

	public function __construct( \wpdb $wpdb ) {
		parent::__construct( $wpdb, ( new TableName() )->paidContent() );
	}

	/**
	 * テーブルを作成します。
	 */
	public function create(): void {
		// リビジョンも含めてレコードが生成されます。
		// 　- 現在の投稿ID -> レコードの上書きあり
		// 　- リビジョンの投稿ID -> レコードの上書きなし
		// 投稿が削除された場合や、リビジョンが削除された場合は
		// このテーブルからも削除されます。(Hooksディレクトリ内を参照)

		$charset = $this->wpdb()->get_charset_collate();
		$sql     = <<<SQL
			CREATE TABLE `{$this->tableName()}` (
				`created_at`                   timestamp            NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`updated_at`                   timestamp            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				`post_id`                      bigint     unsigned  NOT NULL,
				`paid_content`                 longtext             NOT NULL,
				`selling_network_category_id`  int,
				`selling_amount_hex`           varchar(191),
				`selling_decimals`             int,
				`selling_symbol`               varchar(191),
				PRIMARY KEY (`post_id`)
			) {$charset};
		SQL;

		$result = $this->mysqli()->query( $sql );
		assert( true === $result );
	}

	/**
	 * @return null|PaidContentTableRecord
	 */
	public function select( int $post_id ) {
		$sql = <<<SQL
			SELECT `post_id`, `paid_content`, `selling_network_category_id`, `selling_amount_hex`, `selling_decimals`, `selling_symbol`
			FROM `{$this->tableName()}`
			WHERE `post_id` = %d
		SQL;

		$sql = $this->wpdb()->prepare( $sql, $post_id );
		$row = $this->wpdb()->get_row( $sql );

		if ( ! is_null( $row ) ) {
			$row->post_id                     = (int) $row->post_id;
			$row->selling_network_category_id = is_null( $row->selling_network_category_id ) ? null : (int) $row->selling_network_category_id;
			$row->selling_decimals            = is_null( $row->selling_decimals ) ? null : (int) $row->selling_decimals;
		}

		return is_null( $row ) ? null : new PaidContentTableRecord( $row );
	}

	public function set( int $post_id, string $paid_content, ?NetworkCategory $selling_network_category, ?Price $selling_price ): void {
		$selling_network_category_id = is_null( $selling_network_category ) ? null : $selling_network_category->id();
		$selling_price_amount_hex    = is_null( $selling_price ) ? null : $selling_price->amountHex();
		$selling_price_decimals      = is_null( $selling_price ) ? null : $selling_price->decimals();
		$selling_price_symbol        = is_null( $selling_price ) ? null : $selling_price->symbol();

		$sql = <<<SQL
			INSERT INTO `{$this->tableName()}` (
				`post_id`,
				`paid_content`,
				`selling_network_category_id`,
				`selling_amount_hex`,
				`selling_decimals`,
				`selling_symbol`
			) VALUES (
				:post_id, :paid_content, :selling_network_category_id, :selling_amount_hex, :selling_decimals, :selling_symbol
			) ON DUPLICATE KEY UPDATE
				`paid_content` = :paid_content,
				`selling_network_category_id` = :selling_network_category_id,
				`selling_amount_hex` = :selling_amount_hex,
				`selling_decimals` = :selling_decimals,
				`selling_symbol` = :selling_symbol
		SQL;

		$sql = $this->namedPrepare(
			$sql,
			array(
				':post_id'                     => $post_id,
				':paid_content'                => $paid_content,
				':selling_network_category_id' => $selling_network_category_id,
				':selling_amount_hex'          => $selling_price_amount_hex,
				':selling_decimals'            => $selling_price_decimals,
				':selling_symbol'              => $selling_price_symbol,
			)
		);

		$result = $this->wpdb()->query( $sql );

		if ( false === $result ) {
			throw new \Exception( '[8DAB2BCF] Failed to set paid content data.' );
		}
		assert( $result <= 2, "[DBB26475] Failed to set paid content data. - post_id: {$post_id}, result: {$result}" );
	}

	public function delete( int $post_id ): void {
		$sql = <<<SQL
			DELETE FROM `{$this->tableName()}` WHERE `post_id` = %d
		SQL;

		$sql    = $this->wpdb()->prepare( $sql, $post_id );
		$result = $this->wpdb()->query( $sql );

		if ( false === $result ) {
			throw new \Exception( '[C40F74D9] Failed to delete paid content data.' );
		}
		assert( $result <= 1, "[64CF23D9] Failed to delete paid content data. - post_id: {$post_id}, result: {$result}" );
	}

	/**
	 * テーブルが存在するかどうかを取得します。
	 */
	public function exists(): bool {
		return (bool) $this->wpdb()->get_var( "SHOW TABLES LIKE '{$this->tableName()}'" );
	}
}
