<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Features\Repository\SellableSymbols;
use Cornix\Serendipity\Core\Lib\Repository\DefaultRPCURLData;
use Cornix\Serendipity\Core\Lib\Security\Access;
use Cornix\Serendipity\Core\Lib\Web3\Blockchain;

/**
 * Privatenetが有効(このサイトからアクセス可能)かどうかを返すリゾルバです。
 */
class IsPrivatenetEnabledResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return string[]
	 */
	public function resolve( array $root_value, array $args ) {
		// 投稿を新規作成する権限が無い場合はエラーを返す。
		if ( ! ( new Access() )->canCurrentUserCreatePost() ) {
			throw new \LogicException( '[49E3461E] You do not have permission.' );
		}

		// プライベートネットに接続できる場合はtrueを返す。
		$privatenet = new Blockchain( ( new DefaultRPCURLData() )->getPrivatenetL1() );
		return $privatenet->connectable();
	}
}
