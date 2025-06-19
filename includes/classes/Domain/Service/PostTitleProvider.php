<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Service;

interface PostTitleProvider {
	/** 指定された投稿IDのタイトルを取得します。 */
	public function getPostTitle( int $post_id ): ?string;
}
