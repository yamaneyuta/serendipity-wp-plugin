<?php

namespace Cornix\Serendipity\Core\Lib\Repository\Definition\BuiltInRPC;

use Cornix\Serendipity\Core\Lib\Repository\Constants\ChainID;
use Cornix\Serendipity\Core\Types\RpcUrlProviderType;

/**
 * 組み込みのRPC定義
 *
 * 特定のチェーンに対し、どのRPCプロバイダを利用するかを定義します。
 */
class BuiltInRpcProviderDefinition {
	public function __construct() {
		$this->data = array(
			// Mainnet
			ChainID::ETH_MAINNET            => RpcUrlProviderType::ankr(),
			ChainID::POLYGON_ZK_EVM         => RpcUrlProviderType::ankr(),

			// Testnet
			ChainID::SEPOLIA                => RpcUrlProviderType::ankr(),
			ChainID::POLYGON_ZK_EVM_CARDONA => RpcUrlProviderType::ankr(),
			ChainID::SONEIUM_MINATO         => RpcUrlProviderType::soneium(),

			// Privatenet
			ChainID::PRIVATENET_L1          => RpcUrlProviderType::private(),
			ChainID::PRIVATENET_L2          => RpcUrlProviderType::private(),
		);
	}

	/** @var RpcData[] */
	private array $data;

	/**
	 * 指定したチェーンIDに対応する、組み込みのRPCプロバイダを取得します。
	 */
	public function get( int $chain_ID ): ?RpcUrlProviderType {
		return $this->data[ $chain_ID ] ?? null;
	}
}
