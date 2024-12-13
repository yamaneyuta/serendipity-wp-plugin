<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Repository\TokenData;
use Cornix\Serendipity\Core\Lib\Security\Judge;

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
		Judge::checkHasAdminRole();  // 管理者権限が必要

		$tokens = ( new TokenData() )->all();
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
