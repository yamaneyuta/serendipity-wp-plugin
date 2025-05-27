<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Repository\AppContractAddressData;
use Cornix\Serendipity\Core\Lib\Repository\Confirmations;
use Cornix\Serendipity\Core\Lib\Repository\Definition\NetworkCategoryDefinition;
use Cornix\Serendipity\Core\Lib\Repository\RPC;
use Cornix\Serendipity\Core\Lib\Repository\TokenData;
use Cornix\Serendipity\Core\Lib\Security\Judge;

class ChainResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$chain_ID = $args['chainID'];

		// `AppContractResolver`の作成を省略してコールバックを定義
		// `AppContractResolver`を作成した場合はここの処理を書き換えること。
		$app_contract_callback = function () use ( $chain_ID ) {
			$app_contract_address = ( new AppContractAddressData() )->get( $chain_ID );
			return is_null( $app_contract_address ) ? null : array( 'address' => $app_contract_address );
		};

		$confirmations_callback = function () use ( $chain_ID ) {
			// 権限チェック不要
			// 待機ブロック数を返す
			$confirmations = ( new Confirmations() )->get( $chain_ID );
			// string型にして返す(GraphQLの定義した型に変換)
			return (string) $confirmations;
		};

		$rpc_url_callback = function () use ( $chain_ID ) {
			Judge::checkHasAdminRole(); // 管理者権限が必要
			return ( new RPC() )->getURL( $chain_ID );
		};

		$tokens_callback = function () use ( $root_value, $chain_ID ) {
			Judge::checkHasAdminRole(); // 管理者権限が必要

			return array_map(
				function ( $token ) use ( $root_value ) {
					return $root_value['token'](
						$root_value,
						array(
							'chainID' => $token->chainID(),
							'address' => $token->address(),
						)
					);
				},
				( new TokenData() )->select( $chain_ID )
			);
		};

		$network_category_callback = function () use ( $root_value, $chain_ID ) {
			Judge::checkHasAdminRole(); // 管理者権限が必要

			return $root_value['networkCategory'](
				$root_value,
				array(
					'networkCategoryID' => ( new NetworkCategoryDefinition() )->get( $chain_ID )->id(),
				)
			);
		};

		return array(
			'id'              => $chain_ID,
			'appContract'     => $app_contract_callback,
			'confirmations'   => $confirmations_callback,
			'rpcURL'          => $rpc_url_callback,
			'tokens'          => $tokens_callback,
			'networkCategory' => $network_category_callback,
		);
	}
}
