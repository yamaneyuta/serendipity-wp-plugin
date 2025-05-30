<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Security\Judge;
use Cornix\Serendipity\Core\ValueObject\NetworkCategory;

class NetworkCategoriesResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var array */
		$filter                  = $args['filter'] ?? null;
		$filter_network_category = NetworkCategory::from( $filter['networkCategoryID'] ?? null );

		Judge::checkHasEditableRole();  // 投稿編集者権限以上が必要

		// ネットワークカテゴリIDが指定されていない場合は全てのネットワークカテゴリを取得
		$network_categories = is_null( $filter_network_category ) ? NetworkCategory::all() : array( $filter_network_category );

		return array_map(
			fn ( $network_category ) => $root_value['networkCategory']( $root_value, array( 'networkCategoryID' => $network_category->id() ) ),
			$network_categories
		);
	}
}
