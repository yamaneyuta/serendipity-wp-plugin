<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Web3;

use Cornix\Serendipity\Core\Logger\Logger;
use Cornix\Serendipity\Core\Utils\Constants;
use Cornix\Serendipity\Core\Utils\LocalPath;
use Cornix\Serendipity\Core\Utils\TypeValidator;

class ChainId {

	/**
	 * 指定したチェーンIDがメインネットのものかどうかを判定します。
	 *
	 * @param int $chain_id
	 * @return bool
	 */
	public static function isMainnet( int $chain_id ): bool {
		return self::isChainIdIncludes( $chain_id, 'mainnet' );
	}

	/**
	 * 指定したチェーンIDがテストネットのものかどうかを判定します。
	 *
	 * @param int $chain_id
	 * @return bool
	 */
	public static function isTestnet( int $chain_id ): bool {
		return self::isChainIdIncludes( $chain_id, 'testnet' );
	}

	/**
	 * 指定したチェーンIDがプライベートネットのものかどうかを判定します。
	 *
	 * @param int $chain_id
	 * @return bool
	 */
	public static function isPrivatenet( int $chain_id ): bool {
		return self::isChainIdIncludes( $chain_id, 'privatenet' );
	}

	/**
	 * 指定したチェーンIDが指定したネットワークに属するかどうかを判定します。
	 *
	 * @param int    $chain_id
	 * @param string $network_type
	 * @return bool
	 */
	public static function isChainIdIncludes( int $chain_id, string $network_type ): bool {
		if ( TypeValidator::isNetworkType( $network_type ) ) {
			// 対象のネットワーク種別に対応するチェーンID一覧を取得
			$chain_ids = Constants::get( "networks.{$network_type}.chainIds" );
			// 引数のチェーンIDが含まれているかどうかを返す
			return in_array( $chain_id, $chain_ids, true );
		} else {
			Logger::warn( "[D9B08F4F] Invalid network type: $network_type" );
			return false;
		}
	}

	public static function getNetworkType( int $chain_id ): string {
		$network_types = array( 'mainnet', 'testnet', 'privatenet' );
		foreach ( $network_types as $network_type ) {
			if ( self::isChainIdIncludes( $chain_id, $network_type ) ) {
				return $network_type;
			}
		}

		Logger::error( "[D9B08F4F] Invalid chain ID: $chain_id" );
		throw new \InvalidArgumentException( '{58254384-E85E-4905-B2DA-1D7827BB84F6}' );
	}

	/**
	 * すべてのスマートコントラクトがデプロイされているチェーンID一覧を取得します。
	 *
	 * @return int[]
	 */
	public static function getAllDeployedChainIds(): array {
		$contractMeta = json_decode( file_get_contents( LocalPath::getMainContractMetaDataPath() ) );
		/** @var string[] */
		$networks_str_array = array_keys( (array) $contractMeta->networks );

		// int[]に変換して返す
		return array_map( 'intval', $networks_str_array );
	}

	/**
	 * 指定したネットワーク種別でデプロイ済みのチェーンID一覧を取得します。
	 *
	 * @param string $network_type
	 * @return int[]
	 */
	public static function getDeployedChainIds( string $network_type ): array {
		$all_deployed_chain_ids = self::getAllDeployedChainIds();

		return array_filter(
			$all_deployed_chain_ids,
			function ( int $chain_id ) use ( $network_type ) {
				return self::isChainIdIncludes( $chain_id, $network_type );
			}
		);
	}
}
