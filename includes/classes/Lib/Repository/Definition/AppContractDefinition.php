<?php

namespace Cornix\Serendipity\Core\Lib\Repository\Definition;

use Cornix\Serendipity\Core\Lib\Repository\Constants\ChainID;
use Cornix\Serendipity\Core\Lib\Repository\Environment;
use Cornix\Serendipity\Core\Lib\Web3\Ethers;

/**
 * 本アプリケーション用のコントラクトに関する定義
 */
class AppContractDefinition {
	public function __construct() {
		$this->data = array(
			ChainID::ETH_MAINNET   => Ethers::zeroAddress(),  // TODO: アプリケーションコントラクトアドレスをデプロイ後に設定
			ChainID::SEPOLIA       => Ethers::zeroAddress(),  // TODO: アプリケーションコントラクトアドレスをデプロイ後に設定
			ChainID::PRIVATENET_L1 => '0x8A791620dd6260079BF849Dc5567aDC3F2FdC318',
			ChainID::PRIVATENET_L2 => '0x8A791620dd6260079BF849Dc5567aDC3F2FdC318',
		);
	}
	/** @var array */
	private $data;

	/**
	 * アプリケーションがデプロイされているチェーンIDをすべて取得します。
	 *
	 * @return int[]
	 */
	public function allChainIDs(): array {
		return array_keys( $this->data );
	}

	/**
	 * 指定されたチェーンIDに対応するアプリケーションのコントラクトアドレスを取得します。
	 */
	public function address( int $chain_ID ): ?string {
		return $this->data[ $chain_ID ] ?? null;
	}
}
