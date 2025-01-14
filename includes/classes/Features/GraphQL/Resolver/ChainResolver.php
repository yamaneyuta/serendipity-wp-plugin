<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Repository\AppContract;
use Cornix\Serendipity\Core\Lib\Repository\Confirmations;
use Cornix\Serendipity\Core\Lib\Repository\Definition\NetworkCategoryDefinition;
use Cornix\Serendipity\Core\Lib\Repository\Settings\DefaultValue;
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
		$app_contract_address  = ( new AppContract() )->address( $chain_ID );
		$app_contract_callback = fn() => is_null( $app_contract_address ) ? null : array( 'address' => $app_contract_address );

		$confirmations_callback = function () use ( $chain_ID ) {
			// 権限チェック不要

			// 待機ブロック数を取得(ユーザーによる設定が存在しない場合はデフォルト値を使用)
			$confirmations = ( new Confirmations() )->get( $chain_ID );
			if ( is_null( $confirmations ) ) {
				$confirmations = ( new DefaultValue() )->confirmations( $chain_ID );
			}
			return $confirmations;
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
				( new TokenData() )->get( $chain_ID )
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
			'tokens'          => $tokens_callback,
			'networkCategory' => $network_category_callback,
		);
	}
}
