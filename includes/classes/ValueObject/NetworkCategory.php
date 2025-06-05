<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\ValueObject;

use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\Constant\NetworkCategoryID;
use Cornix\Serendipity\Core\Repository\Environment;

/**
 * ネットワークカテゴリを表すクラス
 */
final class NetworkCategory {

	private function __construct( int $network_category_id ) {
		$this->id = $network_category_id;
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
	public static function from( ?int $network_category_id, Environment $environment = null ): ?NetworkCategory {
		if ( is_null( $network_category_id ) ) {
			return null;
		}
		// ネットワークカテゴリIDとして正しい値が渡されているかどうかを検証
		Validate::checkNetworkCategoryID( $network_category_id );

		// 開発環境でない環境でPrivatenetの値を渡された場合は例外をスローする
		if ( $network_category_id === NetworkCategoryID::PRIVATENET && ! ( $environment ?? new Environment() )->isDevelopmentMode() ) {
			throw new \LogicException( '[F9EF95EC] Invalid network category ID. - network_category_id: ' . $network_category_id );
		}

		return new self( $network_category_id );
	}

	public function equals( NetworkCategory $other ): bool {
		return $this->id === $other->id;
	}

	/**
	 * すべてのネットワークカテゴリインスタンスを取得します。
	 * ※ 開発環境でない場合はPrivatenetの値を除外します。
	 *
	 * @return NetworkCategory[]
	 */
	public static function all(): array {
		$environment = new Environment();

		// リフレクションを使用して、クラス定数を取得
		$reflection = new \ReflectionClass( NetworkCategoryID::class );
		$constants  = $reflection->getConstants();
		/** @var int[] */
		$all_network_category_ids = array_values( $constants );

		// 開発環境でない場合はPrivatenetの値を除外する
		if ( ! $environment->isDevelopmentMode() ) {
			$all_network_category_ids = array_values(
				array_filter(
					$all_network_category_ids,
					fn ( int $id ) => $id !== NetworkCategoryID::PRIVATENET
				)
			);
		}

		return array_map(
			fn ( int $network_category_id ) => self::from( $network_category_id, $environment ),
			$all_network_category_ids
		);
	}
}
