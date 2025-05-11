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

	public function __construct( Environment $environment = null ) {
		$this->environment = $environment ?? new Environment();
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
			$addresses[ ChainID::PRIVATENET_L1 ] = '0x5FbDB2315678afecb367f032d93F642f64180aa3';
			$addresses[ ChainID::PRIVATENET_L2 ] = '0xe7f1725E7734CE288F8367e1Bb143E90bb3F0512';
			$addresses[ ChainID::SEPOLIA ]       = '0x6e98081f56608E3a9414823239f65c0e6399561d';
		}

		return array_map(
			fn( int $chain_ID, string $address ) => AppContractType::from( $chain_ID, $address ),
			array_keys( $addresses ),
			array_values( $addresses )
		);
	}
}
