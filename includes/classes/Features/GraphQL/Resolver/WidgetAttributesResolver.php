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

		// 投稿が公開済み、または編集可能な権限がある時に設定されている価格を返します。
		if ( ! $this->isPublishedOrEditable( $post_ID ) ) {
			throw new \LogicException( '[719FA721] You do not have permission to access this post.' );
		}

		// 投稿設定を取得します。
		return ( new WidgetAttributes( new PostContent( $post_ID ) ) )->get();
	}
}
