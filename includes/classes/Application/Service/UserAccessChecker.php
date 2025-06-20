<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

class UserAccessChecker {
	public function __construct( UserAccessProvider $user_access_provider ) {
		$this->user_access_provider = $user_access_provider;
	}

	private UserAccessProvider $user_access_provider;

	/** 現在のユーザーで指定した投稿を閲覧できるかをチェックし、閲覧できない場合は例外をスローします */
	public function checkCanViewPost( int $post_id ): void {
		if ( ! $this->user_access_provider->canViewPost( $post_id ) ) {
			throw new \LogicException( '[E14DA20F] You do not have permission to view this post. post_id: ' . $post_id );
		}
	}

	/** 現在のユーザーで指定した投稿を編集できるかをチェックし、編集できない場合は例外をスローします */
	public function checkCanEditPost( int $post_id ): void {
		if ( ! $this->user_access_provider->canEditPost( $post_id ) ) {
			throw new \LogicException( '[28E55890] You do not have permission to edit this post. post_id: ' . $post_id );
		}
	}

	/** 現在のユーザーが管理者権限を持っているかどうかをチェックし、持っていない場合は例外をスローします */
	public function checkHasAdminRole(): void {
		if ( ! $this->user_access_provider->hasAdminRole() ) {
			throw new \LogicException( '[E61EB62A] You do not have admin access.' );
		}
	}

	/** 現在のユーザーが投稿を新規作成できるかどうかをチェックし、できない場合は例外をスローします */
	public function checkCanCreatePost(): void {
		if ( ! $this->user_access_provider->canCreatePost() ) {
			throw new \LogicException( '[6332E030] You do not have permission to create a post.' );
		}
	}
}
