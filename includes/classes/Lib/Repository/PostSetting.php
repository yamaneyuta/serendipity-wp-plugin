<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Database\TableName;
use Cornix\Serendipity\Core\Lib\Security\Assert;
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
				`id`,
				`post_id`,
				`selling_amount_hex`,
				`selling_decimals`,
				`selling_symbol`,
				`selling_network`
			FROM `{$this->table_name}`
			WHERE `id` = (
					SELECT MAX(`id`)
					FROM `{$this->table_name}`
					WHERE `post_id` = %d
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
			$selling_network    = (string) $row['selling_network'];

			$price = new PriceType( $selling_amount_hex, $selling_decimals, $selling_symbol );
			return new PostSettingType( $price, $selling_network );
		}
	}

	public function set( int $post_id, PostSettingType $post_setting ) {
		$selling_price = $post_setting->sellingPrice;

		Assert::isPostID( $post_id );
		Assert::isHex( $selling_price->amountHex );
		Assert::isDecimals( $selling_price->decimals );
		Assert::isSellableSymbol( $post_setting->sellingNetwork, $selling_price->symbol );
		Assert::isNetworkType( $post_setting->sellingNetwork );

		$result = $this->wpdb->insert(
			$this->table_name,
			array(
				'id'                 => ( new Ulid() )->toUuid(),
				'post_id'            => $post_id,
				'selling_amount_hex' => $selling_price->amountHex,
				'selling_decimals'   => $selling_price->decimals,
				'selling_symbol'     => $selling_price->symbol,
				'selling_network'    => $post_setting->sellingNetwork,
			),
		);

		assert( 1 === $result );

		return $result;
	}
}
