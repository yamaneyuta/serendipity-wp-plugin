<?php

namespace Cornix\Serendipity\Core\Lib\Repository\Definition\BuiltInRPC;

use Cornix\Serendipity\Core\Lib\Repository\Constants\ChainID;
use Cornix\Serendipity\Core\Types\RpcUrlProviderType;

/**
 * RPCプロバイダとチェーンIDから、RPC URLを取得するクラス
 */
class BuiltInRpcUrlDefinition {
	public function __construct() {
		$this->data = array(
			RpcUrlProviderType::ankr()->name()    => new AnkrRpcUrls(),
			RpcUrlProviderType::soneium()->name() => new SoneiumRpcUrls(),
		);
	}

	/** @var array<string,RpcUrlsBase> */
	private array $data;

	/**
	 * 指定したRPCプロバイダとチェーンIDに対応する、RPC URLを取得します。
	 */
	public function get( RpcUrlProviderType $rpc_url_provider_type, int $chain_ID ): ?string {
		$rpc_urls = $this->data[ $rpc_url_provider_type->name() ] ?? null;
		if ( is_null( $rpc_urls ) ) {
			throw new \Exception( '[B92D665F] RPC URL provider not found: ' . $rpc_url_provider_type );
		}

		return $rpc_urls->get( $chain_ID );
	}
}

/**
 * @internal
 */
abstract class RpcUrlsBase {
	/**
	 * 指定したチェーンIDに対応するRPC URLを取得します。
	 * 存在しない場合はnullを返します。
	 */
	abstract public function get( int $chain_ID ): ?string;
}

/**
 * @internal
 */
class AnkrRpcUrls extends RpcUrlsBase {
	/**
	 * @inheritdoc
	 * Chains list: https://www.ankr.com/rpc/
	 */
	public function get( int $chain_ID ): ?string {
		switch ( $chain_ID ) {
			// mainnet
			case ChainID::ETH_MAINNET:
				return 'https://rpc.ankr.com/eth';  // https://www.ankr.com/rpc/eth/
			case ChainID::POLYGON_ZK_EVM:
				return 'https://rpc.ankr.com/polygon_zkevm'; // https://www.ankr.com/rpc/polygon_zkevm/
			// testnet
			case ChainID::SEPOLIA:
				return 'https://rpc.ankr.com/eth_sepolia'; // https://www.ankr.com/rpc/eth/
			case ChainID::POLYGON_ZK_EVM_CARDONA:
				return 'https://rpc.ankr.com/polygon_zkevm_cardona'; // https://www.ankr.com/rpc/polygon_zkevm/
			default:
				return null;
		}
	}
}

/**
 * @internal
 */
class SoneiumRpcUrls extends RpcUrlsBase {
	/**
	 * @inheritdoc
	 */
	public function get( int $chain_ID ): ?string {
		switch ( $chain_ID ) {
			// mainnet
			// TODO: メインネットのRPC URLを追加する
			// testnet
			case ChainID::SONEIUM_MINATO:
				return 'https://rpc.minato.soneium.org';  // https://docs.soneium.org/docs/builders/overview
			default:
				return null;
		}
	}
}
