<?php

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Definition\AppContractDefinition;
use Cornix\Serendipity\Core\Lib\Repository\Environment;
use Cornix\Serendipity\Core\Types\AppContractType;

/**
 * 本アプリケーション用のコントラクトに関する情報を提供します
 */
class AppContract {
	public function __construct( Environment $environment = null ) {
		$this->definition = new AppContractDefinition( $environment ?? new Environment() );
	}
	private AppContractDefinition $definition;

	/**
	 * 指定されたチェーンIDに対応するアプリケーションのコントラクト情報を取得します。
	 *
	 * @param int $chain_ID
	 * @return null|AppContractType
	 */
	public function get( int $chain_ID ): ?AppContractType {
		$app_contracts = array_filter(
			$this->definition->all(),
			fn( AppContractType $app_contract ) => $app_contract->chainID() === $chain_ID
		);
		return array_values( $app_contracts )[0] ?? null;
	}
}
