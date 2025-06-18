<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\Service\ChainService;
use Cornix\Serendipity\Core\Domain\Repository\OracleRepository;
use Cornix\Serendipity\Core\Domain\Specification\OraclesFilter;
use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\Infrastructure\Web3\Ethers;
use Cornix\Serendipity\Core\Infrastructure\Web3\TokenClient;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;
use Cornix\Serendipity\Core\Domain\ValueObject\SymbolPair;

/**
 * ERC20トークンの情報をブロックチェーンから取得して返します。
 */
class GetERC20InfoResolver extends ResolverBase {

	public function __construct( ChainService $chain_service, OracleRepository $oracle_repository ) {
		$this->chain_service     = $chain_service;
		$this->oracle_repository = $oracle_repository;
	}

	private ChainService $chain_service;
	private OracleRepository $oracle_repository;

	/**
	 * #[\Override]
	 *
	 * @return string|null
	 */
	public function resolve( array $root_value, array $args ) {
		Validate::checkHasAdminRole();  // 管理者権限が必要

		$chain_ID = new ChainID( $args['chainID'] );
		$address  = new Address( $args['address'] );

		if ( $address === Ethers::zeroAddress() ) {
			// ERC20トークンの情報を取得するResolverのため、アドレスゼロも不許可
			throw new \InvalidArgumentException( '[6D00DB41] address is zero address.' );
		}

		$chain = $this->chain_service->getChain( $chain_ID );
		if ( is_null( $chain ) ) {
			throw new \InvalidArgumentException( '[DC8E36E6] chain data is not found. chain id: ' . $chain_ID );
		} elseif ( ! $chain->connectable() ) {
			// チェーンが接続可能でない場合は例外を投げる
			throw new \InvalidArgumentException( '[84752B42] not connectable. chain id: ' . $chain_ID );
		}

		$token_client = new TokenClient( $chain->rpcURL(), $address );

		$symbol = $token_client->symbol();

		$symbol_callback = function () use ( $symbol ) {
			Validate::checkHasAdminRole();  // 管理者権限が必要
			return $symbol;
		};

		// レート変換可能かどうかを返すコールバック関数
		$rate_exchangeable_callback = function () use ( $symbol ) {
			Validate::checkHasAdminRole();  // 管理者権限が必要

			$oracles = $this->oracle_repository->all();
			// XXX/USD や XXX/ETH の接続可能なOracleが存在する場合はレート変換可能と判定
			$quote_symbols = array( 'USD', 'ETH' );
			foreach ( $quote_symbols as $quote_symbol ) {
				$filtered_oracles = ( new OraclesFilter() )
					->bySymbolPair( new SymbolPair( $symbol, $quote_symbol ) )
					->byConnectable()
					->apply( $oracles );
				if ( count( $filtered_oracles ) > 0 ) {
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
