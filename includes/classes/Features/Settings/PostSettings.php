<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\Settings;

/**
 * 投稿に関する設定を取得するクラス。
 */
class PostSettings {

	/**
	 * 指定したIDの投稿が公開されているかどうかを返します。
	 *
	 * @param int $post_ID
	 */
	public function isPublished( int $post_ID ): bool {
		return get_post_status( $post_ID ) === 'publish';
	}
}
