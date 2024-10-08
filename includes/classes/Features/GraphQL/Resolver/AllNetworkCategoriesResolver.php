<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Security\Access;
use Cornix\Serendipity\Core\Types\NetworkCategory;

class AllNetworkCategoriesResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		// 新規に投稿を作成可能なユーザーの場合のみすべてのネットワークカテゴリ情報を取得可能
		if ( ! ( new Access() )->canCurrentUserCreatePost() ) {
			throw new \LogicException( '[3A6EF76B] You do not have permission.' );
		}

		$network_categories = array(
			NetworkCategory::mainnet(),
			NetworkCategory::testnet(),
		);

		// プライベートネットが使用可能かどうかを取得
		// TODO:
		error_log( '[6CABBBEA] Not implemented yet: get privatenet enabled' );
		$is_privatenet_enabled = true;  // TODO: 未実装
		if ( $is_privatenet_enabled ) {
			$network_categories[] = NetworkCategory::privatenet();
		}

		return array_map(
			function ( $network_category ) use ( $root_value ) {
				return $root_value['NetworkCategory']( $root_value, array( 'networkCategoryID' => $network_category->id() ) );
			},
			$network_categories
		);
	}
}
