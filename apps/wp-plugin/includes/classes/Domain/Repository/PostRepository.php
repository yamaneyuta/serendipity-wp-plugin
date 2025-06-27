<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Domain\Repository;

use Cornix\Serendipity\Core\Domain\Entity\Post;
use Cornix\Serendipity\Core\Domain\ValueObject\PostId;

interface PostRepository {

	/** 指定した投稿IDに合致する投稿情報を取得します。 */
	public function get( PostId $post_id ): Post;

	/** 投稿情報を保存します。 */
	public function save( Post $post ): void;
}
