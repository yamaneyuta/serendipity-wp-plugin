<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository\Constants;

use Cornix\Serendipity\Core\Repository\Environment;

final class NetworkCategoryID {
	/** メインネット(Ethereumメインネット、Polygonメインネット等) */
	public const MAINNET = 1;
	/** テストネット(Ethereum Sepolia等) */
	public const TESTNET = 2;
	/** プライベートネット(Ganache、Hardhat等) */
	public const PRIVATENET = 3;

	public static function all(): array {
		// リフレクションを使用して、クラス定数を取得
		$reflection = new \ReflectionClass( self::class );
		$constants  = $reflection->getConstants();
		/** @var int[] */
		$all_network_category_ids = array_values( $constants );

		// 開発モードでない場合はプライベートネットのカテゴリIDを除外
		if ( ! ( new Environment() )->isDevelopmentMode() ) {
			$all_network_category_ids = array_filter(
				$all_network_category_ids,
				function ( int $category_id ): bool {
					return $category_id !== NetworkCategoryID::PRIVATENET;
				}
			);
		}

		return array_values( $all_network_category_ids );
	}
}
