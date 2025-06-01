<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Repository\TokenData;
use Cornix\Serendipity\Core\Lib\Security\Validate;

class TokensResolver extends ResolverBase {

	/**
	 * サイトに登録されているトークン一覧を取得します。
	 *
	 * ネイティブトークン + 管理者が追加したERC20トークンの一覧
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		Validate::checkHasAdminRole();  // 管理者権限が必要

		$filter = $args['filter'] ?? null;
		/** @var int|null */
		$filter_chain_ID = $filter['chainID'] ?? null;
		/** @var string|null */
		$filter_address = $filter['address'] ?? null;

		$tokens = ( new TokenData() )->select( $filter_chain_ID, $filter_address );
		return array_map(
			fn( $token ) => $root_value['token'](
				$root_value,
				array(
					'chainID' => $token->chainID(),
					'address' => $token->address(),
				)
			),
			$tokens
		);
	}
}
