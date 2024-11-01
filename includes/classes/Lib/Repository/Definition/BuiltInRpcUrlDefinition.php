<?php

namespace Cornix\Serendipity\Core\Lib\Repository\Definition;

use Cornix\Serendipity\Core\Lib\Repository\Constants\ChainID;
use Cornix\Serendipity\Core\Lib\Repository\Environment;

// サイト所有者(販売者)がRPC URLを設定画面で設定するまでの間、一時的に使用するRPC URLを定義。
// Ethereumの公式ページに載っているサービス等を用いてRPC URLを取得することを推奨。
// -> https://ethereum.org/ja/enterprise/#products-and-services

/**
 * 組み込みのRPC URL定義
 */
class BuiltInRpcUrlDefinition {
	public function __construct() {

		// プライベートネットのURLを取得する関数
		$privatenet = function ( int $number ): string {
			assert( in_array( $number, array( 1, 2 ) ) );
			$prefix = ( new Environment() )->isDevelopmentMode() ? 'tests-' : '';
			return "http://{$prefix}privatenet-{$number}.local";
		};

		// 参考:
		// - https://www.ankr.com/rpc/
		// 　- Rate limits: 1800/min https://www.ankr.com/docs/rpc-service/service-plans/#rate-limits
		// - https://publicnode.com/
		// - https://tokenswap.exchange/tools/chainlist
		$this->data = array(
			new RpcUrlData( ChainID::ETH_MAINNET, 'https://rpc.ankr.com/eth' ),              // https://www.ankr.com/rpc/eth/
			// ↓`https://cloudflare-eth.com`はGitHub Actionsでのテストが通らないため、コメントアウト
			// new RpcUrlData(  ChainID::ETH_MAINNET, 'https://cloudflare-eth.com' ),            // https://developers.cloudflare.com/web3/ethereum-gateway/reference/supported-networks/
			new RpcUrlData( ChainID::ETH_MAINNET, 'https://ethereum-rpc.publicnode.com' ),   // https://ethereum.publicnode.com/

			new RpcUrlData( ChainID::POLYGON_ZK_EVM, 'https://zkevm-rpc.com' ),              // * https://support.polygon.technology/support/solutions/articles/82000893127-how-to-add-zkevm-network-to-metamask-
			new RpcUrlData( ChainID::POLYGON_ZK_EVM, 'https://rpc.ankr.com/polygon_zkevm' ), // https://www.ankr.com/rpc/polygon_zkevm/

			new RpcUrlData( ChainID::SEPOLIA, 'https://rpc.ankr.com/eth_sepolia' ),              // https://www.ankr.com/rpc/eth/
			new RpcUrlData( ChainID::SEPOLIA, 'https://ethereum-sepolia-rpc.publicnode.com' ),   // https://ethereum.publicnode.com/?sepolia

			new RpcUrlData( ChainID::POLYGON_ZK_EVM_CARDONA, 'https://rpc.cardona.zkevm-rpc.com' ),          // * https://docs.polygon.technology/zkEVM/get-started/quick-start/#manually-add-network-to-wallet
			new RpcUrlData( ChainID::POLYGON_ZK_EVM_CARDONA, 'https://rpc.ankr.com/polygon_zkevm_cardona' ), // https://www.ankr.com/rpc/polygon_zkevm/

			new RpcUrlData( ChainID::SONEIUM_MINATO, 'https://rpc.minato.soneium.org' ), // * https://docs.soneium.org/docs/builders/overview

			new RpcUrlData( ChainID::PRIVATENET_L1, $privatenet( 1 ) ),
			new RpcUrlData( ChainID::PRIVATENET_L2, $privatenet( 2 ) ),
		);
	}

	/** @var RpcUrlData[] */
	private array $data;

	/**
	 * 指定したチェーンIDに対応する組み込みのRPC URL一覧を取得します。
	 */
	public function getUrls( int $chain_ID ): array {

		$filtered = array_filter(
			$this->data,
			fn( RpcUrlData $rpc_data ) => $rpc_data->chainID() === $chain_ID
		);

		return array_map(
			fn( RpcUrlData $rpc_data ) => $rpc_data->rpcURL(),
			array_values( $filtered )
		);
	}
}

/** @internal */
class RpcUrlData {
	public function __construct( int $chain_ID, string $rpc_url ) {
		$this->chain_ID = $chain_ID;
		$this->rpc_url  = $rpc_url;
	}
	private int $chain_ID;
	private string $rpc_url;

	public function chainID(): int {
		return $this->chain_ID;
	}

	public function rpcURL(): string {
		return $this->rpc_url;
	}
}
