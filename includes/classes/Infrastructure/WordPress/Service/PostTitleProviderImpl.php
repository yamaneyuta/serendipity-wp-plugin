<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Domain\Service\PostTitleProvider;
use Cornix\Serendipity\Core\Domain\ValueObject\PostId;

class PostTitleProviderImpl implements PostTitleProvider {

	/** @inheritdoc */
	public function getPostTitle( PostId $post_id ): ?string {
		$post = get_post( $post_id->value() );
		return $post ? $post->post_title : null;
	}
}
