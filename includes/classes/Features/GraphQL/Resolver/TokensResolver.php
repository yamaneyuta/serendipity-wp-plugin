<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Domain\Entity\Token;
use Cornix\Serendipity\Core\Domain\Specification\TokensFilter;
use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\Repository\TokenRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;

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
		/** @var Address|null */
		$filter_address = Address::from( $filter['address'] ?? null );

		$tokens_filter = new TokensFilter();
		if ( null !== $filter_chain_ID ) {
			$tokens_filter = $tokens_filter->byChainID( $filter_chain_ID );
		}
		if ( null !== $filter_address ) {
			$tokens_filter = $tokens_filter->byAddress( $filter_address );
		}

		$tokens = $tokens_filter->apply( ( new TokenRepository() )->all() );
		return array_map(
			fn( Token $token ) => $root_value['token'](
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
