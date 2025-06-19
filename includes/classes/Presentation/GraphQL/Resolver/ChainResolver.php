<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\Service\ChainService;
use Cornix\Serendipity\Core\Domain\Entity\Token;
use Cornix\Serendipity\Core\Domain\Repository\AppContractRepository;
use Cornix\Serendipity\Core\Domain\Repository\TokenRepository;
use Cornix\Serendipity\Core\Domain\Specification\TokensFilter;
use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;

class ChainResolver extends ResolverBase {

	public function __construct(
		AppContractRepository $app_contract_repository,
		TokenRepository $token_repository,
		ChainService $chain_service
	) {
		$this->app_contract_repository = $app_contract_repository;
		$this->token_repository        = $token_repository;
		$this->chain_service           = $chain_service;
	}

	private AppContractRepository $app_contract_repository;
	private TokenRepository $token_repository;
	private ChainService $chain_service;

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		$chain_ID = new ChainID( $args['chainID'] );

		$chain = $this->chain_service->getChain( $chain_ID );

		if ( is_null( $chain ) ) {
			throw new \InvalidArgumentException( '[CA31D9B5] chain data is not found. chain id: ' . $chain_ID );
		}

		// `AppContractResolver`の作成を省略してコールバックを定義
		// `AppContractResolver`を作成した場合はここの処理を書き換えること。
		$app_contract_callback = function () use ( $chain ) {
			// 権限チェック不要
			$app_contract = $this->app_contract_repository->get( $chain->id() );
			$address      = is_null( $app_contract ) ? null : $app_contract->address();
			return is_null( $address ) ? null : array( 'address' => $address->value() );
		};

		$tokens_callback = function () use ( $root_value, $chain ) {
			Validate::checkHasAdminRole(); // 管理者権限が必要

			$tokens_filter = ( new TokensFilter() )->byChainID( $chain->id() );
			$tokens        = $tokens_filter->apply( $this->token_repository->all() );

			return array_map(
				function ( Token $token ) use ( $root_value ) {
					return $root_value['token'](
						$root_value,
						array(
							'chainID' => $token->chainID()->value(),
							'address' => $token->address()->value(),
						)
					);
				},
				$tokens
			);
		};

		$network_category_callback = function () use ( $root_value, $chain ) {
			Validate::checkHasAdminRole(); // 管理者権限が必要

			return $root_value['networkCategory'](
				$root_value,
				array(
					'networkCategoryID' => $chain->networkCategoryID()->value(),
				)
			);
		};

		return array(
			'id'              => $chain->id()->value(),
			'appContract'     => $app_contract_callback,
			'confirmations'   => (string) $chain->confirmations(),    // string型にして返す(GraphQLの定義した型に変換)
			'rpcURL'          => $chain->rpcURL(),
			'tokens'          => $tokens_callback,
			'networkCategory' => $network_category_callback,
		);
	}
}
