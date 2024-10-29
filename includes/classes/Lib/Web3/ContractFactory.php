<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Web3;

use Cornix\Serendipity\Core\Lib\Repository\Settings\Config;
use Web3\Contract;
use Web3\Providers\HttpProvider;

class ContractFactory {
	/**
	 * コントラクトのインスタンスを生成します。
	 */
	public function create( string $rpc_url, array $abi, string $address, string $default_block = 'latest' ): Contract {
		$provider = new HttpProvider( $rpc_url, Config::BLOCKCHAIN_REQUEST_TIMEOUT );

		return ( new Contract( $provider, $abi, $default_block ) )->at( $address );
	}
}
