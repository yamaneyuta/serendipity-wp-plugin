<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

interface UserAccessProvider {
	/**
	 * 対象の投稿が閲覧可能かどうかを取得します。
	 *
	 * 以下の条件の場合、閲覧可能と判定します。
	 *   - 投稿が公開済み
	 *   - 投稿は非公開だが編集可能
	 */
	public function canViewPost( int $post_id ): bool;

	public function canEditPost( int $post_id ): bool;

	/** 現在のユーザーが管理者権限を持っているかどうかを取得します。 */
	public function hasAdminRole(): bool;

	/** 現在のユーザーが投稿を新規作成できるかどうかを取得します。 */
	public function canCreatePost(): bool;
}
