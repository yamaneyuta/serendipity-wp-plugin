<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Types;

use Cornix\Serendipity\Core\Lib\Security\Judge;
use Cornix\Serendipity\Core\Repository\Constants\NetworkCategoryID;
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
	 * ネットワークカテゴリを表すインスタンスのキャッシュ。
	 *
	 * @var NetworkCategory[]
	 */
	private static array $cache = array();

	/**
	 * ネットワークカテゴリID(数値)からインスタンスを取得します。
	 * 引数がnullの場合はnullを返します。
	 */
	public static function from( ?int $network_category_id, Environment $environment = null ): ?NetworkCategory {
		if ( is_null( $network_category_id ) ) {
			return null;
		}

		// キャッシュに存在する場合はキャッシュから取得
		if ( isset( self::$cache[ $network_category_id ] ) ) {
			return self::$cache[ $network_category_id ];
		}

		// 開発環境でない環境でPrivatenetの値を渡された場合は例外をスローする
		if ( $network_category_id === NetworkCategoryID::PRIVATENET && ! ( $environment ?? new Environment() )->isDevelopmentMode() ) {
			throw new \LogicException( '[F9EF95EC] Invalid network category ID. - network_category_id: ' . $network_category_id );
		}

		// ネットワークカテゴリIDとして正しい値が渡されているかどうかを検証
		Judge::checkNetworkCategoryID( $network_category_id );

		assert( ! isset( self::$cache[ $network_category_id ] ), '[87F07910] NetworkCategory cache is already set. network_category_id: ' . $network_category_id );
		self::$cache[ $network_category_id ] = new NetworkCategory( $network_category_id );

		return self::$cache[ $network_category_id ];
	}

	/**
	 * メインネットを表すネットワークカテゴリインスタンスを取得します。
	 */
	public static function mainnet(): NetworkCategory {
		return self::from( NetworkCategoryID::MAINNET );
	}

	/**
	 * テストネットを表すネットワークカテゴリインスタンスを取得します。
	 */
	public static function testnet(): NetworkCategory {
		return self::from( NetworkCategoryID::TESTNET );
	}

	/**
	 * プライベートネットを表すネットワークカテゴリインスタンスを取得します。
	 */
	public static function privatenet(): NetworkCategory {
		return self::from( NetworkCategoryID::PRIVATENET );
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
