<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository;

use Cornix\Serendipity\Core\Constant\Config;
use Cornix\Serendipity\Core\Entity\AppContract;

class AppContractRepository {
	public function __construct( ?ChainRepository $chain_repository = null, ?Environment $environment = null ) {
		$this->chain_repository = $chain_repository ?? new ChainRepository();
		$this->environment      = $environment ?? new Environment();
	}
	private ChainRepository $chain_repository;
	private Environment $environment;


	public function get( int $chain_id ): ?AppContract {
		$chain   = $this->chain_repository->getChain( $chain_id );
		$address = $this->getAddress( $chain_id );
		if ( is_null( $chain ) || is_null( $address ) ) {
			return null;
		}
		return new AppContract( $chain, $address );
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
