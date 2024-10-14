<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

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

		$title_callback           = fn() => get_the_title( $post_ID );
		$selling_price_callback   = fn() => $root_value['SellingPrice']( $root_value, array( 'postID' => $post_ID ) );
		$selling_content_callback = fn() => $root_value['SellingContent']( $root_value, array( 'postID' => $post_ID ) );

		return array(
			'id'             => $post_ID,
			'title'          => $title_callback,
			'sellingPrice'   => $selling_price_callback,
			'sellingContent' => $selling_content_callback,
		);
	}
}
