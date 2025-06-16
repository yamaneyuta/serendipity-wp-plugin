<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Application\Factory\ChainRepositoryFactory;
use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategory;
use wpdb;

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

		Validate::checkHasEditableRole();  // 投稿編集者権限以上が必要

		// ネットワークカテゴリIDが指定されていない場合は全てのネットワークカテゴリを取得
		$network_categories = is_null( $filter_network_category )
			? ( new GetAllNetworkCategories( $GLOBALS['wpdb'] ) )->handle()
			: array( $filter_network_category );

		return array_map(
			fn ( $network_category ) => $root_value['networkCategory']( $root_value, array( 'networkCategoryID' => $network_category->id() ) ),
			$network_categories
		);
	}
}

/** すべてのネットワークカテゴリを取得します */
class GetAllNetworkCategories {
	public function __construct( wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}
	private wpdb $wpdb;

	/** @return NetworkCategory[] */
	public function handle(): array {
		$chain_repository = ( new ChainRepositoryFactory( $this->wpdb ) )->create();
		$all_chains       = $chain_repository->getAllChains();

		$network_categories = array();
		foreach ( $all_chains as $chain ) {
			$network_categories[ $chain->networkCategory()->id() ] = $chain->networkCategory();
		}

		return array_values( $network_categories );
	}
}
