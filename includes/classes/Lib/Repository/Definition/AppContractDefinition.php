<?php

namespace Cornix\Serendipity\Core\Lib\Repository\Definition;

use Cornix\Serendipity\Core\Lib\Repository\Constants\ChainID;
use Cornix\Serendipity\Core\Lib\Repository\Environment;
use Cornix\Serendipity\Core\Lib\Web3\Ethers;
use Cornix\Serendipity\Core\Types\AppContractType;

/**
 * 本アプリケーション用のコントラクトに関する定義
 */
class AppContractDefinition {

	public function __construct( Environment $environment ) {
		$this->environment = $environment;
	}

	private Environment $environment;

	/**
	 * アプリケーション用のコントラクト情報をすべて取得します。
	 * ※ 開発環境のみ、プライベートネットのコントラクトアドレスを含みます。
	 *
	 * @return AppContractType[]
	 */
	public function all(): array {
		$addresses = array(
			ChainID::ETH_MAINNET => Ethers::zeroAddress(),  // TODO: アプリケーションコントラクトアドレスをデプロイ後に設定
			ChainID::SEPOLIA     => Ethers::zeroAddress(),  // TODO: アプリケーションコントラクトアドレスをデプロイ後に設定
		);

		if ( $this->environment->isDevelopmentMode() ) {
			$addresses[ ChainID::PRIVATENET_L1 ] = '0x8A791620dd6260079BF849Dc5567aDC3F2FdC318';
			$addresses[ ChainID::PRIVATENET_L2 ] = '0x8A791620dd6260079BF849Dc5567aDC3F2FdC318';
		}

		return array_map(
			fn( int $chain_ID, string $address ) => AppContractType::from( $chain_ID, $address ),
			array_keys( $addresses ),
			array_values( $addresses )
		);
	}
}
