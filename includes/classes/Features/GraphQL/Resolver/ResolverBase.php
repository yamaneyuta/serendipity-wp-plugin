<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

use Cornix\Serendipity\Core\Lib\Security\Access;
use Cornix\Serendipity\Core\Lib\SystemInfo\WPSettings;

abstract class ResolverBase {
	abstract public function resolve( array $root_value, array $args );

	/**
	 * 投稿が公開済み、または投稿を編集できる権限があるかどうかを返します。
	 */
	protected function isPublishedOrEditable( int $post_ID ): bool {
		return ( new WPSettings() )->isPublished( $post_ID ) || ( new Access() )->canCurrentUserEditPost( $post_ID );
	}
}
