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
	private function isPublishedOrEditable( int $post_ID ): bool {
		return ( new WPSettings() )->isPublished( $post_ID ) || ( new Access() )->canCurrentUserEditPost( $post_ID );
	}

	/**
	 * 投稿が公開済み、または投稿を編集できる権限があるかどうかをチェックします。
	 * 投稿が未公開かつ編集権限がない場合は例外をスローします。
	 */
	protected function checkIsPublishedOrEditable( int $post_ID ): void {
		if ( ! $this->isPublishedOrEditable( $post_ID ) ) {
			// TODO: 現在のユーザーIDと投稿IDをログに記録する
			throw new \LogicException( '[53A33BE5] You do not have permission to access this post.' );
		}
	}
}
