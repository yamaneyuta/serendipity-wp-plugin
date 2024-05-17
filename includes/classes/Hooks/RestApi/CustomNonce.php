<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Hooks\RestApi;

use Cornix\Serendipity\Core\Access\Access;
use Cornix\Serendipity\Core\Database\Database;
use Cornix\Serendipity\Core\Logger\Logger;

class CustomNonce {

	public function __construct() {
		// ゲストユーザーのnonce調整
		add_filter( 'nonce_user_logged_out', array( $this, 'add_filter_nonce_user_logged_out' ) );
	}


	/**
	 * ゲストユーザー(ログインしていないユーザー)の場合、`user_id`が`0`固定となる。
	 * 結果として、ゲストユーザーはnonce値が固定の値となってしまうので、
	 * 仮のユーザーIDが割り当てる状況であれば、そのユーザーIDを生成して返す。
	 *
	 * @param int $user_id ユーザーID(他のプラグイン等で割り当てられてない場合、0が入っている)
	 */
	public function add_filter_nonce_user_logged_out( $user_id ): int {
		// ここを通る時はログインしていないユーザーの時のみ。
		if ( 0 !== get_current_user_id() ) {
			Logger::error( 'get_current_user_id: ' . get_current_user_id() );
			throw new \Exception( '{C720AA62-A6D5-4AA3-8584-10B09F42D4D3}' );
		}
		// すでに他のフィルタによって仮のユーザーIDが割り当てられている場合は、その値を返す。
		if ( 0 !== $user_id ) {
			return $user_id;
		}

		// 仮のユーザIDが生成できる場合は`$user_id`の値を上書きする。
		if ( session_id() ) {
			// セッションIDが取得できる場合はそのCRC32ハッシュ値をユーザーIDとする。
			// なお、セッションは本プラグインで開始しない。サイト所有者がphpファイルに直接記述しているか、
			// `WP Session Manager`のようなプラグインを別途インストールしていることを期待している。
			$user_id = crc32( session_id() );
		} elseif ( Access::getIpAddress() && Access::getUserAgent() ) {
			// IPアドレスとユーザーエージェントが取得できる場合。
			// 推測できない固定値としてoptionテーブルに保存しているnonce_saltを使用する。
			$user_id = crc32( Access::getIpAddress() . Access::getUserAgent() . Database::getNonceSalt() );
		}

		// 万が一既存のユーザーIDと衝突した場合は、ユーザーIDを振り直す。
		if ( 0 !== $user_id ) {
			$users = get_users( array( 'fields' => array( 'ID' ) ) );
			do {
				$is_conflict = false;
				foreach ( $users as $user ) {
					if ( intval( $user->ID ) === $user_id ) {
						Logger::warn( 'Conflict user_id: ' . $user_id );
						$is_conflict = true;
						break;
					}
				}
				if ( $is_conflict ) {
					$user_id = crc32( (string) $user_id );
					continue;
				}
			} while ( false );
		}

		return $user_id;
	}
}
