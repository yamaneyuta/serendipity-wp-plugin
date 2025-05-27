<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Database\Schema\PaidContentTable;
use Cornix\Serendipity\Core\Lib\Repository\AppContract;
use Cornix\Serendipity\Core\Lib\Repository\Definition\NetworkCategoryDefinition;
use Cornix\Serendipity\Core\Lib\Repository\RPC;
use Cornix\Serendipity\Core\Types\NetworkCategory;

class VerifiableChainsResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return array
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$post_ID = $args['postID'];

		// 投稿は公開済み、または編集可能な権限があることをチェック
		$this->checkIsPublishedOrEditable( $post_ID );

		$selling_network_category = NetworkCategory::from( ( new PaidContentTable() )->getSellingNetworkCategoryID( $post_ID ) );

		if ( is_null( $selling_network_category ) ) {
			// 通常ネットワークカテゴリ一覧が取得できない場合は無い
			throw new \InvalidArgumentException( '[F105287A] Cannot get selling network category. postID: ' . $post_ID );
		}

		// 投稿の販売ネットワークカテゴリに属する全てのチェーンIDを取得
		$chain_IDs = ( new NetworkCategoryDefinition() )->getAllChainID( $selling_network_category );

		$result = array();
		foreach ( $chain_IDs as $chain_ID ) {
			// アプリケーションコントラクトがデプロイされており、RPC URLが登録されている場合、検証可能なチェーンとして返す
			if ( ! is_null( ( new AppContract() )->get( $chain_ID ) ) && ( new RPC() )->isUrlRegistered( $chain_ID ) ) {
				$result[] = $root_value['chain']( $root_value, array( 'chainID' => $chain_ID ) );
			}
		}

		return $result;
	}
}
