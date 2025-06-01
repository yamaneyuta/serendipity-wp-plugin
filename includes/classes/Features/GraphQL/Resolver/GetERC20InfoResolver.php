<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Service\OracleService;
use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\Lib\Web3\Ethers;
use Cornix\Serendipity\Core\Lib\Web3\TokenClient;
use Cornix\Serendipity\Core\Repository\ChainRepository;
use Cornix\Serendipity\Core\ValueObject\SymbolPair;

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
		Validate::checkHasAdminRole();  // 管理者権限が必要

		/** @var int */
		$chain_ID = $args['chainID'];
		/** @var string */
		$address = $args['address'];

		Validate::checkChainID( $chain_ID );  // チェーンIDが有効であること
		Validate::checkAddress( $address );  // アドレスが有効であること
		if ( $address === Ethers::zeroAddress() ) {
			// ERC20トークンの情報を取得するResolverのため、アドレスゼロも不許可
			throw new \InvalidArgumentException( '[6D00DB41] address is zero address.' );
		}

		$chain = ( new ChainRepository() )->getChain( $chain_ID );
		if ( is_null( $chain ) ) {
			throw new \InvalidArgumentException( '[DC8E36E6] chain data is not found. chain id: ' . $chain_ID );
		} elseif ( ! $chain->connectable() ) {
			// チェーンが接続可能でない場合は例外を投げる
			throw new \InvalidArgumentException( '[84752B42] not connectable. chain id: ' . $chain_ID );
		}

		$token_client = new TokenClient( $chain->rpc_url, $address );

		$symbol = $token_client->symbol();

		$symbol_callback = function () use ( $symbol ) {
			Validate::checkHasAdminRole();  // 管理者権限が必要
			return $symbol;
		};

		// レート変換可能かどうかを返すコールバック関数
		$rate_exchangeable_callback = function () use ( $symbol ) {
			Validate::checkHasAdminRole();  // 管理者権限が必要
			$oracle = new OracleService();
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
