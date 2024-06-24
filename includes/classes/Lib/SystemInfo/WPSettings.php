<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\SystemInfo;

class WPSettings {
	/**
	 * 指定したIDの投稿が公開されているかどうかを返します。
	 *
	 * @param int $post_ID
	 */
	public function isPublished( int $post_ID ): bool {
		return get_post_status( $post_ID ) === 'publish';
	}
}
