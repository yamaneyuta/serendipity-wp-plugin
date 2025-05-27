<?php

namespace Cornix\Serendipity\Core\Repository;

use Cornix\Serendipity\Core\Config\Config;

/**
 * 本アプリケーション用のコントラクトのアドレスを取得するクラス
 */
class AppContractAddressData {

	public function __construct( Environment $environment = null ) {
		$this->environment = is_null( $environment ) ? new Environment() : $environment;
	}

	private Environment $environment;

	/**
	 * 指定されたチェーンIDに対応するAppコントラクトアドレスを取得します。
	 */
	public function get( int $chain_ID ): ?string {
		if ( $this->environment->isDevelopmentMode() ) {
			return Config::DEV_APP_CONTRACT_ADDRESSES[ $chain_ID ] ?? null;
		} else {
			return Config::APP_CONTRACT_ADDRESSES[ $chain_ID ] ?? null;
		}
	}
}
