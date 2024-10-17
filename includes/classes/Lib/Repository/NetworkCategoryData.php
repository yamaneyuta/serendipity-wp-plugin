<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Constants\ChainID;
use Cornix\Serendipity\Core\Types\NetworkCategory;

class NetworkCategoryData {

	private const NETWORK_CATEGORY_INDEX = 0;
	private const CHAIN_ID_INDEX         = 1;

	public function __construct() {
		$mainnet    = NetworkCategory::mainnet()->id();
		$testnet    = NetworkCategory::testnet()->id();
		$privatenet = NetworkCategory::privatenet()->id();

		$this->chain_id_data = array(
			array( $mainnet, ChainID::ETH_MAINNET ),
			array( $testnet, ChainID::SEPOLIA ),
			array( $privatenet, ChainID::PRIVATENET_L1 ),
			array( $privatenet, ChainID::PRIVATENET_L2 ),
		);
	}

	private array $chain_id_data;

	/**
	 * 指定したネットワークカテゴリに含まれるチェーンID一覧を取得します。
	 *
	 * @param NetworkCategory $network_category
	 * @return int[]
	 */
	public function getAllChainID( NetworkCategory $network_category ): array {
		$chain_ids = array();
		foreach ( $this->chain_id_data as $data ) {
			if ( $data[ self::NETWORK_CATEGORY_INDEX ] === $network_category->id() ) {
				$chain_ids[] = $data[ self::CHAIN_ID_INDEX ];
			}
		}
		return $chain_ids;
	}

	/**
	 * 指定したチェーンIDに対応するネットワークカテゴリを取得します。
	 * 該当するデータが存在しない場合はnullを返します。
	 *
	 * @param int $chain_id
	 * @return NetworkCategory|null
	 */
	public function getNetworkCategory( int $chain_id ): ?NetworkCategory {
		foreach ( $this->chain_id_data as $data ) {
			if ( $data[ self::CHAIN_ID_INDEX ] === $chain_id ) {
				return NetworkCategory::from( $data[ self::NETWORK_CATEGORY_INDEX ] );
			}
		}
		return null;
	}
}
