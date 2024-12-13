<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Types;

use Cornix\Serendipity\Core\Lib\Repository\Environment;

/**
 * ネットワークカテゴリを表すクラス
 */
final class NetworkCategory {

	private const NETWORK_CATEGORY_ID_MAINNET    = 1;   // メインネット(Ethereumメインネット、Polygonメインネット等)
	private const NETWORK_CATEGORY_ID_TESTNET    = 2;   // テストネット(Ethereum Sepolia等)
	private const NETWORK_CATEGORY_ID_PRIVATENET = 3;   // プライベートネット(Ganache、Hardhat等)

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
		if ( ! isset( self::$cache[ $network_category_id ] ) ) {
			if ( ! in_array( $network_category_id, array( self::NETWORK_CATEGORY_ID_MAINNET, self::NETWORK_CATEGORY_ID_TESTNET, self::NETWORK_CATEGORY_ID_PRIVATENET ), true ) ) {
				throw new \InvalidArgumentException( '[E878BC2D] Invalid network category ID. - network_category_id: ' . $network_category_id );
			}
			self::$cache[ $network_category_id ] = new NetworkCategory( $network_category_id );
		}

		assert( isset( self::$cache[ $network_category_id ] ), '[79AD75D5] NetworkCategory cache is not set.' );
		return self::$cache[ $network_category_id ];
	}

	/**
	 * メインネットを表すネットワークカテゴリインスタンスを取得します。
	 */
	public static function mainnet(): NetworkCategory {
		return self::from( self::NETWORK_CATEGORY_ID_MAINNET );
	}

	/**
	 * テストネットを表すネットワークカテゴリインスタンスを取得します。
	 */
	public static function testnet(): NetworkCategory {
		return self::from( self::NETWORK_CATEGORY_ID_TESTNET );
	}

	/**
	 * プライベートネットを表すネットワークカテゴリインスタンスを取得します。
	 */
	public static function privatenet(): NetworkCategory {
		return self::from( self::NETWORK_CATEGORY_ID_PRIVATENET );
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
			case self::NETWORK_CATEGORY_ID_MAINNET:
				return 'Mainnet';
			case self::NETWORK_CATEGORY_ID_TESTNET:
				return 'Testnet';
			case self::NETWORK_CATEGORY_ID_PRIVATENET:
				return 'Privatenet';
			default:
				throw new \LogicException( '[E3A7D1A1] Invalid network category ID. id: ' . $this->id() );
		}
	}
}
