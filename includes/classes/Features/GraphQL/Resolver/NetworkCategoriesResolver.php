<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Repository\Environment;
use Cornix\Serendipity\Core\Lib\Security\Judge;
use Cornix\Serendipity\Core\Types\NetworkCategory;

class NetworkCategoriesResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var array */
		$filter                     = $args['filter'] ?? null;
		$filter_network_category_id = $filter['networkCategoryID'] ?? null;

		Judge::checkHasEditableRole();  // 投稿編集者権限以上が必要

		$network_categories = $this->networkCategories( $filter_network_category_id );

		return array_map(
			fn ( $network_category ) => $root_value['networkCategory']( $root_value, array( 'networkCategoryID' => $network_category->id() ) ),
			$network_categories
		);
	}


	/**
	 * 情報を取得するネットワークカテゴリ一覧
	 *
	 * @param null|int $network_category_id 取得するネットワークカテゴリID。指定しない場合はすべてのネットワークカテゴリを取得
	 * @return NetworkCategory[]
	 */
	private function networkCategories( ?int $network_category_id ) {
		// フィルタでネットワークカテゴリIDが指定されている場合はそのネットワークカテゴリのみ取得
		// フィルタが指定されていない場合はすべてのネットワークカテゴリを取得
		$network_categories = is_null( $network_category_id ) ? NetworkCategory::all() : array( NetworkCategory::from( $network_category_id ) );

		if ( empty( $network_categories ) ) {
			// 通常ネットワークカテゴリ一覧が空になることは無い
			throw new \InvalidArgumentException( '[66C34D5A] Invalid network category ID. - network_category_id: ' . $network_category_id );
		}

		return $network_categories;
	}
}
