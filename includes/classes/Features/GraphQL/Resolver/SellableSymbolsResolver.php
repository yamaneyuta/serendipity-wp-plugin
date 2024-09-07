<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Features\Repository\SellableSymbols;
use Cornix\Serendipity\Core\Lib\Security\Access;
use Cornix\Serendipity\Core\Lib\Web3\Blockchain;
use Cornix\Serendipity\Core\Types\NetworkCategory;

/**
 * 販売価格として設定可能な通貨シンボル一覧を返すリゾルバです。
 */
class SellableSymbolsResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return string[]
	 */
	public function resolve( array $root_value, array $args ) {
		// 新規に投稿を作成可能なユーザーの場合のみ販売価格として設定可能な通貨シンボル一覧を取得可能。
		if ( ! ( new Access() )->canCurrentUserCreatePost() ) {
			throw new \LogicException( '[1AC4F136] You do not have permission.' );
		}

		/** @var int */
		$network_category_ID = $args['networkCategoryID'];
		$network_category    = NetworkCategory::from( $network_category_ID );

		// プライベートネットが指定され、プライベートネットに接続できない場合はnullを返す。
		if ( $network_category === NetworkCategory::privatenet() && ! Blockchain::isPrivatenetConnectable() ) {
			return null;
		}

		return ( new SellableSymbols() )->get( $network_category );
	}
}
