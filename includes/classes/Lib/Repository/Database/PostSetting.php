<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository\Database;

use Cornix\Serendipity\Core\Types\PostSettingType;
use Cornix\Serendipity\Core\Types\PriceType;
use wpdb;
use yamaneyuta\Ulid;

class PostSetting {
	public function __construct( wpdb $wpdb ) {
		$this->wpdb       = $wpdb;
		$this->table_name = TableName::postSettingHistory();
	}

	private wpdb $wpdb;
	private string $table_name;

	/**
	 *
	 * @param int $post_ID
	 */
	public function get( int $post_ID ): ?PostSettingType {

		// idが最大のレコードが現在の設定
		$sql = <<<SQL
			SELECT
				id,
				post_id,
				selling_amount_hex,
				selling_decimals,
				selling_symbol
			FROM `{$this->table_name}`
			WHERE id = (
					SELECT MAX(id)
					FROM `{$this->table_name}`
					WHERE post_id = %d
				)
		SQL;

		$query = $this->wpdb->prepare(
			$sql,
			$post_ID
		);
		$row   = $this->wpdb->get_row( $query, ARRAY_A );

		if ( is_null( $row ) ) {
			return null;
		} else {
			// レコードから値を取得
			$selling_amount_hex = (string) $row['selling_amount_hex'];
			$selling_decimals   = (int) $row['selling_decimals'];
			$selling_symbol     = (string) $row['selling_symbol'];

			$price = new PriceType( $selling_amount_hex, $selling_decimals, $selling_symbol );
			return new PostSettingType( $price );
		}
	}

	public function set( int $post_id, PostSettingType $postSetting ) {
		$id  = ( new Ulid() )->toUuid();
		$sql = <<<SQL
			INSERT INTO `{$this->table_name}` (
				id,
				post_id,
				selling_amount_hex,
				selling_decimals,
				selling_symbol
			) VALUES (
				%s, /* id */
				%d, /* post_id */
				%s, /* selling_amount_hex */
				%d, /* selling_decimals */
				%s  /* selling_symbol */
			);
		SQL;

		$query  = $this->wpdb->prepare(
			$sql,
			$id,
			$post_id,
			$postSetting->price->amountHex,
			$postSetting->price->decimals,
			$postSetting->price->symbol
		);
		$result = $this->wpdb->query( $query );
		assert( 1 === $result );

		return $result;
	}
}
