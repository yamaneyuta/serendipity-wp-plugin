<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Service;

use Cornix\Serendipity\Core\Domain\ValueObject\PostId;

interface PostTitleProvider {
	/** 指定された投稿IDのタイトルを取得します。 */
	public function getPostTitle( PostId $post_id ): ?string;
}
