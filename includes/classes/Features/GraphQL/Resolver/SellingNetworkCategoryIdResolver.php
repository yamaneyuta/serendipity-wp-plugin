<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Repository\WidgetAttributes\WidgetAttributes;

class SellingNetworkCategoryIdResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return int|null
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$post_ID = $args['postID'];

		// 投稿は公開済み、または編集可能な権限があることをチェック
		$this->checkIsPublishedOrEditable( $post_ID );

		// ウィジェットの属性を取得
		$widget_attributes = WidgetAttributes::fromPostID( $post_ID );
		return $widget_attributes ? $widget_attributes->sellingNetworkCategory()->id() : null;
	}
}
