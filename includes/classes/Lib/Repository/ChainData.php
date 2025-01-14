<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Definition\NetworkCategoryDefinition;
use Cornix\Serendipity\Core\Types\ChainType;
use Cornix\Serendipity\Core\Types\NetworkCategory;

/**
 * チェーンの情報を取得するクラス
 */
class ChainData {
	/**
	 * 定義されているすべてのチェーンIDを取得します。
	 *
	 * @return int[]
	 */
	public function allIDs(): array {
		return ( new ChainIDs() )->get();
	}

	/**
	 * 定義されているすべてのチェーン情報を取得します
	 *
	 * @return ChainType[]
	 */
	public function all(): array {
		return array_map(
			fn( $chain_ID ) => $this->get( $chain_ID ),
			( new ChainIDs() )->get(),
		);
	}

	/**
	 * 指定したチェーンIDの情報を取得します。
	 */
	public function get( int $chain_ID ): ChainType {
		return ChainType::from( $chain_ID );
	}
}

/** @internal */
class ChainIDs {
	public function __construct( Environment $environment = null ) {
		if ( self::$environment !== $environment ) {
			// 環境が変わったらキャッシュをクリア
			// (テストでキャッシュが保持されたままになるのを防ぐためのコード)
			self::$environment = $environment;
			self::$cache       = null;
		}
	}

	private static ?Environment $environment = null;
	private static ?array $cache             = null;

	/**
	 * 定義されているチェーンIDをすべて取得します。
	 *
	 * @return int[]
	 */
	public function get(): array {
		if ( self::$cache === null ) {
			// ChainIDクラスに定義されている定数をすべて取得
			$reflectionClass = new \ReflectionClass( 'Cornix\Serendipity\Core\Lib\Repository\Constants\ChainID' );
			$constants       = $reflectionClass->getConstants();
			/** @var int[] */
			$all_chainIDs = array_values( $constants );

			// 本番環境ではプライベートネットのチェーンIDを除外
			if ( ! ( self::$environment ?? new Environment() )->isDevelopmentMode() ) {
				$privatenet_chain_ids = ( new NetworkCategoryDefinition() )->getAllChainID( NetworkCategory::privatenet() );
				$all_chainIDs         = array_diff( $all_chainIDs, $privatenet_chain_ids );
			}

			self::$cache = $all_chainIDs;
		}

		return self::$cache;
	}
}
