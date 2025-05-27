<?php

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Config\Config;

/**
 * 本アプリケーション用のコントラクトのチェーンIDに関する情報を取得するクラス
 */
class AppContractChainIdData {

	public function __construct() {
		$this->environment = new Environment();
	}

	private Environment $environment;

	/**
	 * デプロイ済みのチェーンID一覧を取得します。
	 */
	public function allDeployed(): array {
		if ( $this->environment->isDevelopmentMode() ) {
			return array_keys( Config::DEV_APP_CONTRACT_ADDRESSES );
		} else {
			return array_keys( Config::APP_CONTRACT_ADDRESSES );
		}
	}
}
