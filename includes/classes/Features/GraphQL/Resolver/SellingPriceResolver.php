<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Types\PriceType;

class SellingPriceResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return PriceType|null
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$post_ID = $args['postID'];

		// 投稿が公開済み、または編集可能な権限がある時に設定されている価格を返します。
		if ( ! $this->isPublishedOrEditable( $post_ID ) ) {
			throw new \LogicException( '[1A90BD10] You do not have permission to access this post.' );
		}

		// 投稿設定を取得
		$post_setting = $root_value['postSetting']( $root_value, array( 'postID' => $post_ID ) );
		return $post_setting ? $post_setting->sellingPrice : null;
	}
}
