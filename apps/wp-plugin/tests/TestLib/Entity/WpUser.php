<?php
declare(strict_types=1);

namespace Cornix\Serendipity\TestLib\Entity;

/**
 * WordPressユーザーを表現するクラス
 *
 * テスト中にデータベースの内容が変更されるため、IDは保持せず、ユーザー名で識別します。
 */
class WpUser {
	public const ADMINISTRATOR       = 'admin';  // ユーザー名は`admin`
	public const CONTRIBUTOR         = 'contributor';
	public const ANOTHER_CONTRIBUTOR = 'another_contributor';
	public const VISITOR             = 'visitor';

	private string $name;
	public function __construct( string $name ) {
		if ( ! in_array( $name, array( self::ADMINISTRATOR, self::CONTRIBUTOR, self::ANOTHER_CONTRIBUTOR, self::VISITOR ), true ) ) {
			throw new \InvalidArgumentException( '[54CAAF93] Invalid user name: ' . $name );
		}

		$this->name = $name;
	}

	public function name(): string {
		return $this->name;
	}

	/** ユーザーID。毎回データベースに問い合わせて取得する */
	private function id(): int {
		if ( $this->name === self::VISITOR ) {
			return 0;
		}

		// WordPressのユーザー名からユーザーオブジェクトを取得
		$wp_user = get_user_by( 'login', $this->name );
		if ( $wp_user ) {
			return $wp_user->ID;
		} else {
			assert( $this->name !== self::ADMINISTRATOR, '[5635784D] Administrator user must exist during tests.' );
			assert( $this->name !== self::VISITOR, '[E582D347] Visitor user should not be created.' );

			// ユーザーが存在しない場合は作成する
			switch ( $this->name ) {
				case self::CONTRIBUTOR:
				case self::ANOTHER_CONTRIBUTOR:
					$result = wp_insert_user(
						array(
							'user_login' => $this->name,
							'user_email' => $this->name . '@example.com',
							'user_pass'  => 'password', // パスワードはテスト用なので適当で良い
							'role'       => 'contributor',
						)
					);
					if ( is_wp_error( $result ) ) {
						throw new \RuntimeException( '[2361D4FF] Failed to create user: ' . $result->get_error_message() );
					}
					assert( is_int( $result ), '[5D81C63F] User creation did not return an integer ID.' );
					return $result; // 作成したユーザーのIDを返す
				default:
					// ここは通らない
					throw new \InvalidArgumentException( '[5A605CC7] Invalid user name: ' . $this->name );
			}
		}
	}

	public static function admin(): self {
		return new self( self::ADMINISTRATOR );
	}
	public static function contributor(): self {
		return new self( self::CONTRIBUTOR );
	}
	public static function anotherContributor(): self {
		return new self( self::ANOTHER_CONTRIBUTOR );
	}
	public static function visitor(): self {
		return new self( self::VISITOR );
	}
	public static function current(): self {
		$current_user = wp_get_current_user();
		if ( $current_user->exists() ) {
			return new self( $current_user->user_login );
		}
		return self::visitor(); // 現在のユーザーが存在しない場合は訪問者として扱う
	}
	/** ログインユーザーをこのユーザーに切り替えます */
	public function setCurrent(): void {
		wp_set_current_user( $this->id() );
	}

	public function __toString(): string {
		return $this->name;
	}
}
