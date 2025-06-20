<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Infrastructure\WordPress\Service;

use Cornix\Serendipity\Core\Application\Service\UserAccessProvider;

class UserAccessProviderImpl implements UserAccessProvider {

	/** @inheritdoc */
	public function canViewPost( int $post_id ): bool {
		return current_user_can( 'read_post', $post_id );
	}

	/** @inheritdoc */
	public function canEditPost( int $post_id ): bool {
		return current_user_can( 'edit_post', $post_id );
	}

	/** @inheritdoc */
	public function hasAdminRole(): bool {
		// current_user_can( 'administrator' ) はロールをチェックしているため、
		// ロールの設定が変更された場合に正しく動作しない可能性がある。
		// ここでは管理者が持つ代表的な権限である`manage_options`をチェックする。
		return current_user_can( 'manage_options' );
	}

	/** @inheritdoc */
	public function canCreatePost(): bool {
		// 寄稿者(contributor)以上の権限がある場合、投稿を作成可能。
		// https://wordpress.org/documentation/article/roles-and-capabilities/#capability-vs-role-table
		return current_user_can( 'edit_posts' );
	}
}
