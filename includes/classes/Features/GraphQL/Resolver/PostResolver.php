<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Logger\Logger;
use Cornix\Serendipity\Core\Lib\Repository\Definition\NetworkCategoryDefinition;
use Cornix\Serendipity\Core\Lib\Repository\PaidContentData;
use Cornix\Serendipity\Core\Lib\Repository\PayableTokens;

class PostResolver extends ResolverBase {

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

		return array(
			'id'             => $post_ID,
			'title'          => fn() => get_the_title( $post_ID ),
			'sellingPrice'   => fn() => $root_value['sellingPrice']( $root_value, array( 'postID' => $post_ID ) ),
			'sellingContent' => fn() => $root_value['sellingContent']( $root_value, array( 'postID' => $post_ID ) ),
			'payableTokens'  => fn() => $this->payableTokens( $root_value, $post_ID ),
		);
	}

	/**
	 * 指定された投稿IDに対して支払いが可能なトークン一覧を取得します。
	 */
	private function payableTokens( array $root_value, int $post_ID ) {
		// 販売ネットワークカテゴリを取得
		$selling_network_category = ( new PaidContentData( $post_ID ) )->sellingNetworkCategory();

		if ( is_null( $selling_network_category ) ) {
			Logger::warn( '[21B2C2DD] Selling network category is null for post ID: ' . $post_ID );
		}

		// 投稿に設定されている販売ネットワークカテゴリに属するチェーンID一覧を取得
		$chain_IDs = is_null( $selling_network_category ) ? array() : ( new NetworkCategoryDefinition() )->getAllChainID( $selling_network_category );

		$result = array();
		foreach ( $chain_IDs as $chain_ID ) {
			$payable_tokens = ( new PayableTokens() )->get( $chain_ID );
			foreach ( $payable_tokens as $token ) {
				$result[] = $root_value['token'](
					$root_value,
					array(
						'chainID' => $token->chainID(),
						'address' => $token->address(),
					)
				);
			}
		}

		return $result;
	}
}
