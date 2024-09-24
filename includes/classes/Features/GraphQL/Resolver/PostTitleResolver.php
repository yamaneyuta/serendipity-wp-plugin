<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\GraphQL\Resolver;

class PostTitleResolver extends ResolverBase {

	/**
	 * #[\Override]
	 *
	 * @return string|null
	 */
	public function resolve( array $root_value, array $args ) {
		/** @var int */
		$post_ID = $args['postID'];

		// 投稿は公開済み、または編集可能な権限があることをチェック
		$this->checkIsPublishedOrEditable( $post_ID );

		// 投稿のタイトルを取得して返す
		return get_the_title( $post_ID );
	}
}
