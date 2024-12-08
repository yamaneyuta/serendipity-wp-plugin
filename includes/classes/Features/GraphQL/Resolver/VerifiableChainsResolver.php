<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Repository\AppContract;
use Cornix\Serendipity\Core\Lib\Repository\Definition\NetworkCategoryDefinition;
use Cornix\Serendipity\Core\Lib\Repository\RpcURL;
use Cornix\Serendipity\Core\Lib\Repository\WidgetAttributes;

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

		$selling_network_category = WidgetAttributes::fromPostID( $post_ID )->sellingNetworkCategory() ?? null;

		if ( is_null( $selling_network_category ) ) {
			// 通常ネットワークカテゴリ一覧が取得できない場合は無い
			throw new \InvalidArgumentException( '[F105287A] Cannot get selling network category. postID: ' . $post_ID );
		}

		// 投稿の販売ネットワークカテゴリに属する全てのチェーンIDを取得
		$chain_IDs = ( new NetworkCategoryDefinition() )->getAllChainID( $selling_network_category );

		$result       = array();
		$rpc_url      = new RpcURL();
		$app_contract = new AppContract();
		foreach ( $chain_IDs as $chain_ID ) {
			// ネットワークに接続可能かつ、アプリケーションのコントラクトアドレスが取得できる場合は検証可能なチェーンとして返す
			if ( $rpc_url->isConnectable( $chain_ID ) && in_array( $chain_ID, $app_contract->allChainIDs() ) ) {
				$result[] = $root_value['chain']( $root_value, array( 'chainID' => $chain_ID ) );
			}
		}

		return $result;
	}
}
