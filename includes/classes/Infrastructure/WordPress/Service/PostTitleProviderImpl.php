<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Domain\Service\PostTitleProvider;

class PostTitleProviderImpl implements PostTitleProvider {

	/** @inheritdoc */
	public function getPostTitle( int $post_id ): ?string {
		$post = get_post( $post_id );
		return $post ? $post->post_title : null;
	}
}
