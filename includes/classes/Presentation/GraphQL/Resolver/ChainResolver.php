<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Presentation\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\Dto\TokenDto;
use Cornix\Serendipity\Core\Application\UseCase\GetAppContract;
use Cornix\Serendipity\Core\Application\UseCase\GetChain;
use Cornix\Serendipity\Core\Application\UseCase\GetTokensByChainId;
use Cornix\Serendipity\Core\Lib\Security\Validate;

class ChainResolver extends ResolverBase {

	public function __construct(
		GetChain $get_chain,
		GetAppContract $get_app_contract,
		GetTokensByChainId $get_tokens_by_chain_id
	) {
		$this->get_chain              = $get_chain;
		$this->get_app_contract       = $get_app_contract;
		$this->get_tokens_by_chain_id = $get_tokens_by_chain_id;
	}

	private GetChain $get_chain;
	private GetAppContract $get_app_contract;
	private GetTokensByChainId $get_tokens_by_chain_id;

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$chain_id = $args['chainID'];

		$chain = $this->get_chain->handle( $chain_id );

		if ( is_null( $chain ) ) {
			throw new \InvalidArgumentException( '[CA31D9B5] chain data is not found. chain id: ' . $chain_id );
		}

		// `AppContractResolver`の作成を省略してコールバックを定義
		// `AppContractResolver`を作成した場合はここの処理を書き換えること。
		$app_contract_callback = function () use ( $chain ) {
			// 権限チェック不要
			$app_contract = $this->get_app_contract->handle( $chain->id() );
			$address      = null !== $app_contract ? $app_contract->address() : null;
			return is_null( $address ) ? null : array( 'address' => $address );
		};

		$tokens_callback = function () use ( $root_value, $chain_id ) {
			Validate::checkHasAdminRole(); // 管理者権限が必要

			$tokens = $this->get_tokens_by_chain_id->handle( $chain_id );

			return array_map(
				function ( TokenDto $token ) use ( $root_value, $chain_id ) {
					return $root_value['token'](
						$root_value,
						array(
							'chainID' => $chain_id,
							'address' => $token->address(),
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
					'networkCategoryID' => $chain->networkCategoryId(),
				)
			);
		};

		return array(
			'id'              => $chain->id(),
			'appContract'     => $app_contract_callback,
			'confirmations'   => $chain->confirmations(),
			'rpcURL'          => $chain->rpcUrl(),
			'tokens'          => $tokens_callback,
			'networkCategory' => $network_category_callback,
		);
	}
}
