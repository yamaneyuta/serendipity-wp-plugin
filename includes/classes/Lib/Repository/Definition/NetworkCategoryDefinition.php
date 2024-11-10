<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository\Definition;

use Cornix\Serendipity\Core\Lib\Repository\Constants\ChainID;
use Cornix\Serendipity\Core\Types\NetworkCategory;

class NetworkCategoryDefinition {

	public function __construct() {
		$mainnet    = NetworkCategory::mainnet();
		$testnet    = NetworkCategory::testnet();
		$privatenet = NetworkCategory::privatenet();

		$this->data = array(
			ChainID::ETH_MAINNET            => $mainnet,
			ChainID::POLYGON_ZK_EVM         => $mainnet,
			ChainID::SEPOLIA                => $testnet,
			ChainID::POLYGON_ZK_EVM_CARDONA => $testnet,
			ChainID::SONEIUM_MINATO         => $testnet,
			ChainID::PRIVATENET_L1          => $privatenet,
			ChainID::PRIVATENET_L2          => $privatenet,
		);
	}

	/** @var array<int,NetworkCategory> */
	private array $data;

	/**
	 * 指定されたチェーンIDに対応するネットワークカテゴリを取得します。
	 */
	public function get( int $chain_ID ): NetworkCategory {
		$result = $this->data[ $chain_ID ] ?? null;
		if ( is_null( $result ) ) {
			throw new \InvalidArgumentException( '[96897B8A] Invalid chain ID. - chain_ID: ' . $chain_ID );
		}
		return $result;
	}

	/**
	 * 指定したネットワークカテゴリに含まれるチェーンID一覧を取得します。
	 *
	 * @param NetworkCategory $network_category
	 * @return int[]
	 */
	public function getAllChainID( NetworkCategory $network_category ): array {
		$chain_ids = array();
		foreach ( $this->data as $chain_id => $category ) {
			if ( $category === $network_category ) {
				$chain_ids[] = $chain_id;
			}
		}
		return $chain_ids;
	}
}
