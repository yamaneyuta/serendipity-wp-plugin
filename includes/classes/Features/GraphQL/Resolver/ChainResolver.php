<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Entity\Token;
use Cornix\Serendipity\Core\Lib\Algorithm\Filter\TokensFilter;
use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\Repository\AppContractRepository;
use Cornix\Serendipity\Core\Repository\ChainRepository;
use Cornix\Serendipity\Core\Repository\TokenRepository;

class ChainResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$chain_ID = $args['chainID'];
		$chain    = ( new ChainRepository() )->getChain( $chain_ID );

		if ( is_null( $chain ) ) {
			throw new \InvalidArgumentException( '[CA31D9B5] chain data is not found. chain id: ' . $chain_ID );
		}

		// `AppContractResolver`の作成を省略してコールバックを定義
		// `AppContractResolver`を作成した場合はここの処理を書き換えること。
		$app_contract_callback = function () use ( $chain ) {
			// 権限チェック不要
			$app_contract = ( new AppContractRepository() )->get( $chain->id );
			$address      = is_null( $app_contract ) ? null : $app_contract->address;
			return is_null( $address ) ? null : array( 'address' => $address->value() );
		};

		$tokens_callback = function () use ( $root_value, $chain ) {
			Validate::checkHasAdminRole(); // 管理者権限が必要

			$tokens_filter = ( new TokensFilter() )->byChainID( $chain->id );
			$tokens        = $tokens_filter->apply( ( new TokenRepository() )->all() );

			return array_map(
				function ( Token $token ) use ( $root_value ) {
					return $root_value['token'](
						$root_value,
						array(
							'chainID' => $token->chainID(),
							'address' => $token->address()->value(),
						)
					);
				},
				$tokens->toArray()
			);
		};

		$network_category_callback = function () use ( $root_value, $chain ) {
			Validate::checkHasAdminRole(); // 管理者権限が必要

			return $root_value['networkCategory'](
				$root_value,
				array(
					'networkCategoryID' => $chain->networkCategory()->id(),
				)
			);
		};

		return array(
			'id'              => $chain->id,
			'appContract'     => $app_contract_callback,
			'confirmations'   => (string) $chain->confirmations,    // string型にして返す(GraphQLの定義した型に変換)
			'rpcURL'          => $chain->rpc_url,
			'tokens'          => $tokens_callback,
			'networkCategory' => $network_category_callback,
		);
	}
}
