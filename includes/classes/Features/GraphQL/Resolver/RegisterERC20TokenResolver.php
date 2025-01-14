<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Repository\TokenData;
use Cornix\Serendipity\Core\Lib\Security\Judge;

/**
 * ERC20トークンの情報をサーバーに登録します。
 */
class RegisterERC20TokenResolver extends ResolverBase {

	/**
	 * #[\Override]
	 */
	public function resolve( array $root_value, array $args ) {
		Judge::checkHasAdminRole();  // 管理者権限が必要

		/** @var int */
		$chain_ID = $args['chainID'];
		/** @var string */
		$address = $args['address'];
		/** @var bool */
		$is_payable = $args['isPayable'];

		// ERC20トークンを登録
		( new TokenData() )->add( $chain_ID, $address );
		// 支払可能状態を更新
		if ( $is_payable ) {
			$root_value['addPayableTokens'](
				$root_value,
				array(
					'chainID'        => $chain_ID,
					'tokenAddresses' => array( $address ),
				)
			);
		}

		return true;
	}
}
