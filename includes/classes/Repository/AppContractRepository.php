<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository;

use Cornix\Serendipity\Core\Constants\Config;
use Cornix\Serendipity\Core\Entity\AppContract;

class AppContractRepository {
	public function __construct( ?Environment $environment = null ) {
		$this->environment = $environment ?? new Environment();
	}
	private Environment $environment;


	public function get( int $chain_id ): ?AppContract {
		$address = $this->getAddress( $chain_id );
		if ( $address === null ) {
			return null;
		}
		return new AppContract( $chain_id, $address );
	}

	/**
	 * 指定したチェーンにデプロイされているAppコントラクトのアドレスを取得します。
	 */
	private function getAddress( int $chain_id ): ?string {
		if ( $this->environment->isDevelopmentMode() ) {
			return Config::DEV_APP_CONTRACT_ADDRESSES[ $chain_id ] ?? null;
		} else {
			return Config::APP_CONTRACT_ADDRESSES[ $chain_id ] ?? null;
		}
	}
}
