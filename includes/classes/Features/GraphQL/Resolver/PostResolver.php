<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Database\Schema\PaidContentTable;
use Cornix\Serendipity\Core\Lib\Repository\Definition\NetworkCategoryDefinition;
use Cornix\Serendipity\Core\Lib\Repository\PayableTokens;
use Cornix\Serendipity\Core\Types\NetworkCategory;

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
		// 投稿に設定されている販売ネットワークカテゴリに属するチェーンID一覧を取得
		$chain_IDs = ( new NetworkCategoryDefinition() )->getAllChainID(
			NetworkCategory::from( ( new PaidContentTable() )->getSellingNetworkCategoryID( $post_ID ) )
		);

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
