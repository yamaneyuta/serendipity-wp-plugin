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
		$filter = $args['filter'] ?? null;

		Judge::checkHasEditableRole();  // 投稿編集者権限以上が必要

		$network_categories = $this->networkCategories( $filter['networkCategoryID'] ?? null );

		return array_map(
			function ( $network_category ) use ( $root_value ) {
				return $root_value['networkCategory']( $root_value, array( 'networkCategoryID' => $network_category->id() ) );
			},
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
		if ( ! is_null( $network_category_id ) ) {
			return array( NetworkCategory::from( $network_category_id ) );
		}

		// フィルタが指定されていない場合は全てのネットワークカテゴリを取得
		$network_categories = array(
			NetworkCategory::mainnet(),
			NetworkCategory::testnet(),
		);
		// 開発モードの場合はプライベートネットも取得
		if ( ( new Environment() )->isDevelopmentMode() ) {
			$network_categories[] = NetworkCategory::privatenet();
		}

		return $network_categories;
	}
}
