<?php

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Constants\ChainID;

// サイト所有者(販売者)がRPC URLを設定画面で設定するまでの間、一時的に使用するRPC URLを定義。
// Ethereumの公式ページに載っているサービス等を用いてRPC URLを取得することを推奨。
// -> https://ethereum.org/ja/enterprise/#products-and-services

/**
 * 組み込みのRPC URL定義
 */
class BuiltInRpcUrlDefinition {

	private const CHAIN_ID_INDEX = 0;
	private const RPC_URL_INDEX  = 1;

	public function __construct() {
		// 参考:
		// - https://www.ankr.com/rpc/
		// - https://publicnode.com/
		// - https://tokenswap.exchange/tools/chainlist

		$this->built_in_rpc_data = array(
			// `https://cloudflare-eth.com`はGitHub Actionsでのテストが通らないため、コメントアウト
			array( ChainID::ETH_MAINNET, 'https://rpc.ankr.com/eth' ),              // https://www.ankr.com/rpc/eth/
			// array( ChainID::ETH_MAINNET, 'https://cloudflare-eth.com' ),            // https://developers.cloudflare.com/web3/ethereum-gateway/reference/supported-networks/
			array( ChainID::ETH_MAINNET, 'https://ethereum-rpc.publicnode.com' ),   // https://ethereum.publicnode.com/

			array( ChainID::POLYGON_ZK_EVM, 'https://zkevm-rpc.com' ),              // * https://support.polygon.technology/support/solutions/articles/82000893127-how-to-add-zkevm-network-to-metamask-
			array( ChainID::POLYGON_ZK_EVM, 'https://rpc.ankr.com/polygon_zkevm' ), // https://www.ankr.com/rpc/polygon_zkevm/

			array( ChainID::SEPOLIA, 'https://rpc.ankr.com/eth_sepolia' ),              // https://www.ankr.com/rpc/eth/
			array( ChainID::SEPOLIA, 'https://ethereum-sepolia-rpc.publicnode.com' ),   // https://ethereum.publicnode.com/?sepolia

			array( ChainID::POLYGON_ZK_EVM_CARDONA, 'https://rpc.cardona.zkevm-rpc.com' ),          // * https://docs.polygon.technology/zkEVM/get-started/quick-start/#manually-add-network-to-wallet
			array( ChainID::POLYGON_ZK_EVM_CARDONA, 'https://rpc.ankr.com/polygon_zkevm_cardona' ), // https://www.ankr.com/rpc/polygon_zkevm/

			array( ChainID::SONEIUM_MINATO, 'https://rpc.minato.soneium.org' ), // * https://docs.soneium.org/docs/builders/overview

			array( ChainID::PRIVATENET_L1, $this->privatenet( 1 ) ),
			array( ChainID::PRIVATENET_L2, $this->privatenet( 2 ) ),
		);
	}

	/** @var [int, string][] */
	private array $built_in_rpc_data;

	/**
	 * 指定したチェーンIDに対応する組み込みのRPC URL一覧を取得します。
	 */
	public function getRpcUrls( int $chain_ID ): array {
		$rpc_urls = array();
		foreach ( $this->built_in_rpc_data as $rpc_data ) {
			if ( $rpc_data[ self::CHAIN_ID_INDEX ] === $chain_ID ) {
				$rpc_urls[] = $rpc_data[ self::RPC_URL_INDEX ];
			}
		}
		return $rpc_urls;
	}

	/** PrivatenetのRPC URLを取得します。 */
	private function privatenet( int $number ): string {
		assert( $number === 1 || $number === 2 );
		$prefix = ( new Environment() )->isDevelopmentMode() ? 'tests-' : '';
		return "http://{$prefix}privatenet-{$number}.local";
	}
}
