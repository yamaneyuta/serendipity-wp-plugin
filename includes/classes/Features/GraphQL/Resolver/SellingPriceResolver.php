<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Repository\WidgetAttributes\SellingPrice;
use Cornix\Serendipity\Core\Types\PriceType;
use Cornix\Serendipity\Core\Types\WidgetAttributesType;

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

		// ウィジェットの属性を取得
		/** @var WidgetAttributesType|null */
		$widget_attributes = $root_value['widgetAttributes']( $root_value, array( 'postID' => $post_ID ) );
		// 価格の型に変換して返す
		return $widget_attributes ? SellingPrice::fromWidgetAttributes( $widget_attributes ) : null;
	}
}
