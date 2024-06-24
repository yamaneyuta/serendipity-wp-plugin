<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Security;

class Access {

	/**
	 * ※ APIアクセス時は、`wp_rest`アクションのnonceがヘッダが`X-WP-Nonce`に含まれている必要があります。
	 *
	 * @return void
	 */
	public function __construct() {
	}

	/**
	 * 現在アクセスしているユーザーが、指定した投稿を編集できるかどうかを返します。
	 *
	 * @param int $post_ID
	 */
	public function canCurrentUserEditPost( int $post_ID ): bool {
		return current_user_can( 'edit_post', $post_ID );
	}
}
