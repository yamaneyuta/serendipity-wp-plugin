<?php

declare(strict_types=1);

namespace Cornix\Serendipity\Core\Access;

use Cornix\Serendipity\Core\Utils\Strings;
use Cornix\Serendipity\Core\Utils\Url;

class Access {


	/**
	 * 管理画面からのアクセスかどうかを返します。
	 *
	 * @return bool
	 */
	public static function isAdminReferer(): bool {
		$referer = wp_get_referer();
		if ( false === $referer ) {
			return false;
		}
		$admin_url = get_admin_url();
		return Strings::starts_with( strtolower( $referer ), strtolower( $admin_url ) );
	}

	/**
	 * 記事を編集する画面からのアクセスかどうかを返します。
	 *
	 * @return bool
	 */
	public static function isPostEditScreenReferer(): bool {
		$referer = wp_get_referer();
		if ( false === $referer ) {
			return false;
		}
		return self::isPostEditScreen( $referer );
	}

	/**
	 * URLが記事を編集する画面かどうかを判定します。
	 */
	private static function isPostEditScreen( string $url ): bool {
		$base_name = basename( $url );
		return ( 'post.php' === $base_name || 'post-new.php' === $base_name );
	}

	/**
	 * サイト内からのアクセスかどうかを返します。
	 */
	public static function isSiteReferer(): bool {
		$referer = wp_get_referer();
		if ( false === $referer ) {
			return false;
		}
		$site_address = Url::getSiteAddress();
		return Strings::starts_with( strtolower( $referer ), strtolower( $site_address ) );
	}


	/**
	 * 現在アクセスしているユーザーが管理者権限を持っているかどうかを返します。
	 * ※ APIで正常に動作させるためには、`wp_rest`アクションのnonceがヘッダの`X-WP-Nonce`に含まれている必要がある。
	 */
	public static function isAdminUser(): bool {
		return current_user_can( 'manage_options' );
	}


	/**
	 * 指定した投稿IDの内容を編集する権限を持っているかどうかを返します。
	 * ※ APIで正常に動作させるためには、`wp_rest`アクションのnonceがヘッダの`X-WP-Nonce`に含まれている必要がある。
	 */
	public static function isEditableUser( int $post_id ): bool {
		// TODO: has_create_post_authorityで代用できる箇所があるかチェック
		return current_user_can( 'edit_post', $post_id );
	}

	/**
	 * 指定した投稿IDの内容を閲覧する権限を持っているかどうかを返します。
	 * ※ APIで正常に動作させるためには、`wp_rest`アクションのnonceがヘッダの`X-WP-Nonce`に含まれている必要がある。
	 *
	 * 以下のいずれかの時に`true`を返します。
	 * - 投稿が公開されている
	 * - 投稿の編集権限を持っている
	 *
	 * @param int $post_id
	 * @return bool
	 */
	public static function isPostViewable( int $post_id ): bool {
		if ( get_post_status( $post_id ) === 'publish' ) {
			return true;
		}
		return self::isEditableUser( $post_id );
	}


	/**
	 * クライアントのユーザーエージェントを取得します。
	 */
	public static function getUserAgent(): ?string {
		return isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : null;
	}

	/**
	 * クライアントのIPアドレスを取得します。
	 */
	public static function getIpAddress(): ?string {
		if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			return $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED'] ) ) {
			return $_SERVER['HTTP_X_FORWARDED'];
		} elseif ( isset( $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'] ) ) {
			return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
		} elseif ( isset( $_SERVER['HTTP_FORWARDED_FOR'] ) ) {
			return $_SERVER['HTTP_FORWARDED_FOR'];
		} elseif ( isset( $_SERVER['HTTP_FORWARDED'] ) ) {
			return $_SERVER['HTTP_FORWARDED'];
		} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			return $_SERVER['REMOTE_ADDR'];
		}

		return null;
	}
}
