<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Repository\WidgetAttributes\WidgetAttributes;

class SellingPriceResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return array|null
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$post_ID = $args['postID'];

		// 投稿は公開済み、または編集可能な権限があることをチェック
		$this->checkIsPublishedOrEditable( $post_ID );

		// ウィジェットの属性を取得
		$widget_attributes = WidgetAttributes::fromPostID( $post_ID );;

		// 販売価格を返す
		$selling_price = is_null( $widget_attributes ) ? null : $widget_attributes->sellingPrice();
		return is_null( $selling_price ) ? null : array(
			'amountHex' => $selling_price->amountHex(),
			'decimals'  => $selling_price->decimals(),
			'symbol'    => $selling_price->symbol(),
		);
	}
}
