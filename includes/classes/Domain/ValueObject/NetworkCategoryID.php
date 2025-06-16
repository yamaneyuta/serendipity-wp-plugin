<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

use Cornix\Serendipity\Core\Constant\NetworkCategoryIdValue;

/**
 * ネットワークカテゴリIDを表すクラス
 */
final class NetworkCategoryID {

	public function __construct( int $network_category_id_value ) {
		if ( $network_category_id_value < 1 || $network_category_id_value > 3 ) {
			throw new \InvalidArgumentException( 'Invalid network category ID: ' . $network_category_id_value );
		}
		$this->id = $network_category_id_value;
	}

	/** ネットワークカテゴリID(数値) */
	private int $id;

	/** ネットワークカテゴリIDを数値で取得します。 */
	public function id(): int {
		return $this->id;
	}

	/**
	 * ネットワークカテゴリID(数値)からインスタンスを取得します。
	 * 引数がnullの場合はnullを返します。
	 */
	public static function from( ?int $network_category_id_value ): ?NetworkCategoryID {
		return is_null( $network_category_id_value ) ? null : new self( $network_category_id_value );
	}

	public function equals( NetworkCategoryID $other ): bool {
		return $this->id === $other->id;
	}


	public static function mainnet(): self {
		return new self( NetworkCategoryIdValue::MAINNET );
	}
	public static function testnet(): self {
		return new self( NetworkCategoryIdValue::TESTNET );
	}
	public static function privatenet(): self {
		return new self( NetworkCategoryIdValue::PRIVATENET );
	}
}
