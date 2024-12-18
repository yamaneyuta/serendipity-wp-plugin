<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Repository\ChainData;
use Cornix\Serendipity\Core\Lib\Repository\Oracle;
use Cornix\Serendipity\Core\Lib\Repository\RpcURL;
use Cornix\Serendipity\Core\Lib\Security\Judge;
use Cornix\Serendipity\Core\Lib\Web3\Ethers;
use Cornix\Serendipity\Core\Lib\Web3\TokenClient;
use Cornix\Serendipity\Core\Types\SymbolPair;

/**
 * ERC20トークンの情報をブロックチェーンから取得して返します。
 */
class GetERC20InfoResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return string|null
	 */
	public function resolve( array $root_value, array $args ) {
		Judge::checkHasAdminRole();  // 管理者権限が必要

		/** @var int */
		$chain_ID = $args['chainID'];
		/** @var string */
		$address = $args['address'];

		Judge::checkChainID( $chain_ID );  // チェーンIDが有効であること
		Judge::checkAddress( $address );  // アドレスが有効であること
		if ( $address === Ethers::zeroAddress() ) {
			// ERC20トークンの情報を取得するResolverのため、アドレスゼロも不許可
			throw new \InvalidArgumentException( '[6D00DB41] address is zero address.' );
		}
		if ( ! ( new ChainData() )->get( $chain_ID )->isConnectable() ) {
			// 接続できないチェーンIDが指定された場合も例外を投げる
			throw new \InvalidArgumentException( '[84752B42] chainID is not connectable. chain id: ' . $chain_ID );
		}

		$rpc_url      = ( new RpcURL() )->connectableURL( $chain_ID );
		$token_client = new TokenClient( $rpc_url, $address );

		$symbol = $token_client->symbol();

		$symbol_callback = function () use ( $symbol ) {
			Judge::checkHasAdminRole();  // 管理者権限が必要
			return $symbol;
		};

		// レート変換可能かどうかを返すコールバック関数
		$rate_exchangeable_callback = function () use ( $symbol ) {
			Judge::checkHasAdminRole();  // 管理者権限が必要
			$oracle = new Oracle();
			// XXX/USD や XXX/ETH のOracleが存在する場合はレート変換可能と判定
			$quote_symbols = array( 'USD', 'ETH' );
			foreach ( $quote_symbols as $quote_symbol ) {
				if ( count( $oracle->connectableChainIDs( new SymbolPair( $symbol, $quote_symbol ) ) ) > 0 ) {
					return true;
				}
			}
			return false;
		};

		return array(
			'symbol'           => $symbol_callback,
			'rateExchangeable' => $rate_exchangeable_callback,
		);
	}
}
