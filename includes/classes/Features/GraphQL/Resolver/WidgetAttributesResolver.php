<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Repository\WidgetAttributes\WidgetAttributes;

class WidgetAttributesResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return WidgetAttributes|null
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$post_ID = $args['postID'];

		// 投稿は公開済み、または編集可能な権限があることをチェック
		$this->checkIsPublishedOrEditable( $post_ID );

		return WidgetAttributes::fromPostID( $post_ID );
	}
}
