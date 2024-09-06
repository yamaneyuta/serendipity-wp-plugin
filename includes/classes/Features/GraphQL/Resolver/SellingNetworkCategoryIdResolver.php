<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Types\WidgetAttributesType;

class SellingNetworkCategoryIdResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return int|null
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$post_ID = $args['postID'];

		// 投稿が公開済み、または編集可能な権限がある時に設定されている販売ネットワーク種別を返します。
		if ( ! $this->isPublishedOrEditable( $post_ID ) ) {
			throw new \LogicException( '[A9085BAC] You do not have permission to access this post.' );
		}

		// ウィジェットの属性を取得
		/** @var WidgetAttributesType|null */
		$widget_attributes = $root_value['widgetAttributes']( $root_value, array( 'postID' => $post_ID ) );
		return $widget_attributes ? $widget_attributes->sellingNetworkCategoryID : null;
	}
}
