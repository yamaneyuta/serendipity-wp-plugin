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
	 */
	public static function from( int $network_category_id ): NetworkCategory {
		Judge::checkNetworkCategoryID( $network_category_id );

		if ( ! isset( self::$cache[ $network_category_id ] ) ) {
			self::$cache[ $network_category_id ] = new NetworkCategory( $network_category_id );
		}

		assert( isset( self::$cache[ $network_category_id ] ), '[79AD75D5] NetworkCategory cache is not set.' );
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
	 *
	 * @return NetworkCategory[]
	 */
	public static function all(): array {
		$is_development_mode = ( new Environment() )->isDevelopmentMode();

		$result = array(
			self::mainnet(),
			self::testnet(),
		);
		if ( $is_development_mode ) {
			$result[] = self::privatenet();
		}

		return $result;
	}


	public function __toString(): string {
		switch ( $this->id() ) {
			case NetworkCategoryID::MAINNET:
				return 'Mainnet';
			case NetworkCategoryID::TESTNET:
				return 'Testnet';
			case NetworkCategoryID::PRIVATENET:
				return 'Privatenet';
			default:
				throw new \LogicException( '[E3A7D1A1] Invalid network category ID. id: ' . $this->id() );
		}
	}
}
