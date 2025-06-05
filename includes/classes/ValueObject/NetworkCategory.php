<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\ValueObject;

use Cornix\Serendipity\Core\Constant\NetworkCategoryID;
use Cornix\Serendipity\Core\Repository\Environment;

/**
 * ネットワークカテゴリを表すクラス
 */
final class NetworkCategory {

	private function __construct( int $network_category_id, Environment $environment ) {
		// ネットワークカテゴリIDとして正しい値が渡されているかどうかを検証
		self::checkNetworkCategoryID( $network_category_id, $environment );

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
		return is_null( $network_category_id ) ? null : new self( $network_category_id, $environment ?? new Environment() );
	}

	public function equals( NetworkCategory $other ): bool {
		return $this->id === $other->id;
	}

	/**
	 * 指定したネットワークカテゴリIDが有効な値であるかどうかを検証し、不正な値の場合は例外をスローします。
	 */
	private static function checkNetworkCategoryID( int $network_category_id, Environment $environment ): void {
		if ( ! in_array( $network_category_id, self::allChainIDs( $environment ), true ) ) {
			throw new \InvalidArgumentException( '[CC0F870E] Invalid network category ID. - network_category_id: ' . $network_category_id );
		}
	}

	/**
	 * ネットワークカテゴリIDをすべて取得します
	 * ※ 開発環境でない場合はPrivatenetの値を除外します。
	 */
	private static function allChainIDs( Environment $environment ): array {
		// リフレクションを使用して、クラス定数を取得
		$reflection = new \ReflectionClass( NetworkCategoryID::class );
		$constants  = $reflection->getConstants();
		/** @var int[] */
		$all_chain_ids = array_values( $constants );

		// 開発環境でない場合はPrivatenetの値を除外する
		if ( ! $environment->isDevelopmentMode() ) {
			$all_chain_ids = array_values(
				array_filter(
					$all_chain_ids,
					fn ( int $id ) => $id !== NetworkCategoryID::PRIVATENET
				)
			);
		}

		return $all_chain_ids;
	}

	/**
	 * すべてのネットワークカテゴリインスタンスを取得します。
	 * ※ 開発環境でない場合はPrivatenetの値を除外します。
	 *
	 * @return NetworkCategory[]
	 */
	public static function all(): array {
		$environment = new Environment();
		return array_map(
			fn ( int $network_category_id ) => self::from( $network_category_id, $environment ),
			self::allChainIDs( $environment )
		);
	}
}
