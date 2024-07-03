<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Repository\SellableSymbols;
use Cornix\Serendipity\Core\Lib\Security\Access;

/**
 * 販売価格として設定可能な通貨シンボル一覧を返すリゾルバです。
 */
class SellableSymbolsResolver extends ResolverBase {

	public function __construct() {
		parent::__construct( 'sellableSymbols' );
	}

	/**
	 * #[\Override]
	 *
	 * @return string[]
	 */
	public function resolve( array $root_value, array $args ) {

		// 新規に投稿を作成可能なユーザーの場合のみ販売価格として設定可能な通貨シンボル一覧を取得可能。
		if ( ( new Access() )->canCurrentUserCreatePost() ) {
			return ( new SellableSymbols() )->get();
		}

		throw new \LogicException( '[1AC4F136] You do not have permission.' );
	}
}
