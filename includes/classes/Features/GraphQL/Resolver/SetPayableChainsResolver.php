<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Repository\PayableChainIDs;
use Cornix\Serendipity\Core\Lib\Security\Judge;
use Cornix\Serendipity\Core\Types\NetworkCategory;

class SetPayableChainsResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return bool
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$network_category_id = $args['networkCategoryID'];
		/** @var int[] */
		$chain_ids = $args['chainIDs'];

		// 管理者権限を持っているかどうかをチェック
		Judge::checkIsAdministrator();

		// 購入者が支払可能なチェーンID一覧を保存
		// ※ チェーンIDとネットワークカテゴリの整合性チェックはsaveメソッド内で行われるため、ここでは不要
		( new PayableChainIDs() )->save( NetworkCategory::from( $network_category_id ), $chain_ids );

		return true;
	}
}
