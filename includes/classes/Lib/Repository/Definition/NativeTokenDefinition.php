<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository\Definition;

use Cornix\Serendipity\Core\Lib\Repository\Constants\ChainID;

class NativeTokenDefinition {
	/**
	 * 対象のチェーンIDで使用されているネイティブトークンのシンボルを取得します。
	 */
	public function getSymbol( int $chain_ID ): string {
		switch ( $chain_ID ) {
			case ChainID::ETH_MAINNET:
			case ChainID::POLYGON_ZK_EVM:
			case ChainID::SEPOLIA:
			case ChainID::POLYGON_ZK_EVM_CARDONA:
			case ChainID::SONEIUM_MINATO:
			case ChainID::PRIVATENET_L1:
				return 'ETH';
			case ChainID::PRIVATENET_L2:
				return 'MATIC'; // TODO: OracleでPOL/USD等が取得できるようになったタイミングでPOLへ変更
			default:
				throw new \InvalidArgumentException( '[398C040E] Invalid chain ID. - ' . $chain_ID );
		}
	}
}
