<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Types;

use Cornix\Serendipity\Core\Lib\Security\Judge;

/**
 * アプリケーションコントラクトを表すクラス
 */
class AppContractType {

	private function __construct( int $chain_ID, string $address ) {
		$this->chain_ID = $chain_ID;
		$this->address  = $address;
	}

	private int $chain_ID;
	private string $address;

	public static function from( int $chain_ID, string $contract_address ): AppContractType {
		Judge::checkChainID( $chain_ID );
		Judge::checkAddress( $contract_address );

		return new AppContractType( $chain_ID, $contract_address );
	}

	public function chainID(): int {
		return $this->chain_ID;
	}

	public function address(): string {
		return $this->address;
	}
}
