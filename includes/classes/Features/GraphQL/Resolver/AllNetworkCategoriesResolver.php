<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Repository\Environment;
use Cornix\Serendipity\Core\Lib\Security\Judge;
use Cornix\Serendipity\Core\Types\NetworkCategory;

class AllNetworkCategoriesResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		// 投稿編集者権限以上が必要
		// - 編集画面でのネットワーク選択に使用するため
		Judge::checkHasEditableRole();

		$network_categories = array(
			NetworkCategory::mainnet(),
			NetworkCategory::testnet(),
		);
		// 開発モードの場合はプライベートネットも取得
		if ( ( new Environment() )->isDevelopmentMode() ) {
			$network_categories[] = NetworkCategory::privatenet();
		}

		return array_map(
			function ( $network_category ) use ( $root_value ) {
				return $root_value['networkCategory']( $root_value, array( 'networkCategoryID' => $network_category->id() ) );
			},
			$network_categories
		);
	}
}
