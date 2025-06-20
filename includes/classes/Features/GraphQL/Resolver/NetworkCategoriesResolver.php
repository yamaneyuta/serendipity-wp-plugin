<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Domain\Repository\ChainRepository;
use Cornix\Serendipity\Core\Lib\Security\Validate;
use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategoryID;

class NetworkCategoriesResolver extends ResolverBase {

	public function __construct( ChainRepository $chain_repository ) {
		$this->chain_repository = $chain_repository;
	}

	private ChainRepository $chain_repository;

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var array */
		$filter                  = $args['filter'] ?? null;
		$filter_network_category = NetworkCategoryID::from( $filter['networkCategoryID'] ?? null );

		Validate::checkHasEditableRole();  // 投稿編集者権限以上が必要

		// ネットワークカテゴリIDが指定されていない場合は全てのネットワークカテゴリを取得
		$network_category_ids = is_null( $filter_network_category )
			? ( new GetAllNetworkCategoryIDs( $this->chain_repository ) )->handle()
			: array( $filter_network_category );

		return array_map(
			fn ( $network_category_id ) => $root_value['networkCategory']( $root_value, array( 'networkCategoryID' => $network_category_id->value() ) ),
			$network_category_ids
		);
	}
}

/** すべてのネットワークカテゴリIDを取得します */
class GetAllNetworkCategoryIDs {
	public function __construct( ChainRepository $chain_repository ) {
		$this->chain_repository = $chain_repository;
	}
	private ChainRepository $chain_repository;

	/** @return NetworkCategoryID[] */
	public function handle(): array {
		$all_chains = $this->chain_repository->all();

		$network_categories = array();
		foreach ( $all_chains as $chain ) {
			$network_categories[ $chain->networkCategoryID()->value() ] = $chain->networkCategoryID();
		}

		return array_values( $network_categories );
	}
}
