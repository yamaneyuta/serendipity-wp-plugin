<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\ValueObject\Address;

class HardhatAccount {
	/**
	 * コントラクトをデプロイするアカウント。
	 */
	public function deployer(): Address {
		return new Address( '0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266' );
	}

	/**
	 * hardhat環境においてすべてのコントラクトがデプロイされた後に残高が増やされるアカウント。
	 * このアカウントの残高が0ETHでなくなってからテストを実施する。
	 */
	public function marker(): Address {
		return new Address( '0x0000000000000000000000000000000000000001' );
	}
}
