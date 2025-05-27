<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository\Constants;

use Cornix\Serendipity\Core\Repository\ChainData;
use Cornix\Serendipity\Core\Repository\Environment;

final class ChainID {
	// ==================== Mainnet ====================
	/** イーサリアムメインネット(L1) */
	public const ETH_MAINNET = 1;


	/** Polygon zkEVM(L2/mainnet) */
	public const POLYGON_ZK_EVM = 1101; // 0x44d

	// ==================== Testnet ====================
	/** イーサリアムSepoliaテストネット(L1) */
	public const SEPOLIA = 11155111;    // 0xaa36a7


	/** Polygon zkEVMテストネット(L2/Sepolia) */
	public const POLYGON_ZK_EVM_CARDONA = 2442; // 0x98a

	/** Soneiumテストネット(L2/Sepolia) */
	public const SONEIUM_MINATO = 1946; // 0x79a

	// ==================== Privatenet ====================
	/** PrivatenetL1に位置付けられたチェーンID */
	public const PRIVATENET_L1 = 31337; // 0x7a69


	/** PrivatenetL2に位置付けられたチェーンID(L2) ※実際はロールアップを行っていない、単に独立したネットワーク */
	public const PRIVATENET_L2 = 1337;  // 0x539


	/**
	 * 指定したネットワークカテゴリIDに対応するチェーンID一覧を取得します。
	 *
	 * @param int|null $target_network_category_id 対象のネットワークカテゴリID(ネットワークカテゴリを限定しない場合はnullを指定)
	 */
	public static function all( int $target_network_category_id = null ): array {
		// リフレクションを使用して、クラス定数を取得
		$reflection = new \ReflectionClass( self::class );
		$constants  = $reflection->getConstants();
		/** @var int[] */
		$all_chainIDs = array_values( $constants );

		// ネットワークカテゴリIDに対応するチェーンIDをフィルタリング
		if ( ! is_null( $target_network_category_id ) ) {
			$all_chainIDs = array_filter(
				$all_chainIDs,
				function ( int $chainID ) use ( $target_network_category_id ): bool {
					return ( new ChainData( $chainID ) )->networkCategory()->id() === $target_network_category_id;
				}
			);
		}

		// 開発モードでない場合はプライベートネットのチェーンIDを除外
		if ( ! ( new Environment() )->isDevelopmentMode() ) {
			$all_chainIDs = array_filter(
				$all_chainIDs,
				function ( int $chainID ): bool {
					return ( new ChainData( $chainID ) )->networkCategory()->id() !== NetworkCategoryID::PRIVATENET;
				}
			);
		}

		return array_values( $all_chainIDs );
	}
}
