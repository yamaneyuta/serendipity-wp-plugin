<?php

declare(strict_types=1);

namespace Cornix\Serendipity\Core\Access;

class Nonce {

	const CREATE_NONCE_ACTION_VIEW   = '3efa6255';    // 表示用(購入者用)
	const CREATE_NONCE_ACTION_ADMIN  = '0025ba43';    // 管理者用
	const CREATE_NONCE_ACTION_EDITOR = 'b158f779';    // 投稿編集者用

	// nonceが指定されるクエリパラメータ名。
	// `check_ajax_referer`や`check_admin_referer`で使用。
	const QUERY_ARG = '_ajax_nonce';

	/**
	 * 管理者用のnonceを作成します。
	 *
	 * @return string nonce
	 */
	public static function createAdminActionNonce(): string {
		return wp_create_nonce( self::CREATE_NONCE_ACTION_ADMIN );
	}

	/**
	 * 投稿編集者用のnonceを作成します。
	 *
	 * @return string
	 */
	public static function createEditorActionNonce(): string {
		return wp_create_nonce( self::CREATE_NONCE_ACTION_EDITOR );
	}

	/**
	 * 表示用(購入者用)のnonceを作成します。
	 *
	 * @return string
	 */
	public static function createViewActionNonce(): string {
		return wp_create_nonce( self::CREATE_NONCE_ACTION_VIEW );
	}


	/**
	 * nonceをチェックします。
	 * ※ CSRF対策。
	 */
	private static function verifyAjaxNonce( string $action ): bool {
		// `check_ajax_referer`という名称だが、実際はnonceのチェックのみ。(action未指定時はリファラチェックが行われるが、action未指定が非推奨)
		// falseが返ってきた場合はfalseを返し、それ以外(1 or 2)の場合はnonceが有効なのでtrueを返す。
		return check_ajax_referer( $action, self::QUERY_ARG, false ) === false ? false : true;
	}


	/**
	 * 管理者用のnonceをチェックします。
	 *
	 * @return bool
	 */
	public static function verifyAdminNonce(): bool {
		return self::verifyAjaxNonce( self::CREATE_NONCE_ACTION_ADMIN );
	}

	/**
	 * 投稿編集者用のnonceをチェックします。
	 *
	 * @return bool
	 */
	public static function verifyEditableUserNonce(): bool {
		return self::verifyAjaxNonce( self::CREATE_NONCE_ACTION_EDITOR );
	}

	/**
	 * 表示用(購入者用)のnonceをチェックします。
	 *
	 * @return bool
	 */
	public static function verifyViewNonce(): bool {
		return self::verifyAjaxNonce( self::CREATE_NONCE_ACTION_VIEW );
	}
}
