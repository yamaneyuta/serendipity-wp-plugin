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

		return ( new RetryContract( $provider, $abi, $default_block ) )->at( $address );
	}
}

/**
 * コントラクト呼び出しをリトライ処理ありで実行するクラス
 */
class RetryContract extends Contract {
	/**
	 * construct
	 *
	 * @param string|\Web3\Providers\Provider $provider
	 * @param string|\stdClass|array          $abi
	 * @param mixed                           $defaultBlock
	 * @return void
	 */
	public function __construct( $provider, $abi, $defaultBlock = 'latest' ) {
		parent::__construct( $provider, $abi, $defaultBlock );
	}

	/** @inheritdoc */
	public function call( ...$args ) {
		return ( new BlockchainRetryer() )->execute( fn() => parent::call( ...$args ), Config::BLOCKCHAIN_REQUEST_RETRY_INTERVALS_MS );
	}
}
