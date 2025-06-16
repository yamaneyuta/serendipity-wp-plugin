<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\ValueObject;

use Cornix\Serendipity\Core\Constant\NetworkCategoryIdValue;

/**
 * ネットワークカテゴリIDを表すクラス
 */
final class NetworkCategoryID {

	public function __construct( int $network_category_id_value ) {
		if ( $network_category_id_value < NetworkCategoryIdValue::MIN || $network_category_id_value > NetworkCategoryIdValue::MAX ) {
			throw new \InvalidArgumentException( '[6AD1DCA2] Invalid network category ID: ' . $network_category_id_value );
		}
		$this->value = $network_category_id_value;
	}

	/** ネットワークカテゴリID(数値) */
	private int $value;

	/** ネットワークカテゴリIDを数値で取得します。 */
	public function value(): int {
		return $this->value;
	}

	/**
	 * ネットワークカテゴリID(数値)からインスタンスを取得します。
	 * 引数がnullの場合はnullを返します。
	 */
	public static function from( ?int $network_category_id_value ): ?NetworkCategoryID {
		return is_null( $network_category_id_value ) ? null : new self( $network_category_id_value );
	}

	public function equals( NetworkCategoryID $other ): bool {
		return $this->value === $other->value;
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
