<?php

namespace Cornix\Serendipity\Core\Repository;

use Cornix\Serendipity\Core\Constants\Config;

/**
 * 指定したチェーンのAppコントラクトの情報を取得するクラス
 */
class AppContractData {

	public function __construct( int $chain_ID, Environment $environment = null ) {
		$this->chain_ID    = $chain_ID;
		$this->environment = is_null( $environment ) ? new Environment() : $environment;
	}

	private int $chain_ID;
	private Environment $environment;

	/**
	 * 指定されたチェーンIDに対応するAppコントラクトアドレスを取得します。
	 */
	public function address(): ?string {
		if ( $this->environment->isDevelopmentMode() ) {
			return Config::DEV_APP_CONTRACT_ADDRESSES[ $this->chain_ID ] ?? null;
		} else {
			return Config::APP_CONTRACT_ADDRESSES[ $this->chain_ID ] ?? null;
		}
	}
}
