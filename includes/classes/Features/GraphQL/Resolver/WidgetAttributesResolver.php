<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Post\PostContent;
use Cornix\Serendipity\Core\Lib\Repository\WidgetAttributes\WidgetAttributes;

class WidgetAttributesResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return WidgetAttributesType|null
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$post_ID = $args['postID'];

		// 投稿設定を取得します。
		return ( new WidgetAttributes( new PostContent( $post_ID ) ) )->get();
	}
}
