<?php
declare(strict_types=1);

class WpdbFactory {
	/**
	 * 指定したホスト名に接続するwpdbを返します。
	 *
	 * @param string $host
	 * @return wpdb
	 */
	public static function create( string $host ): wpdb {
		assert( strpos( $host, 'mysql' ) !== false || strpos( $host, 'mariadb' ) !== false );
		if ( $host === $GLOBALS['wpdb']->dbhost ) {
			return $GLOBALS['wpdb'];
		}

		// phpcs:ignore
		// phpcsでフォーマットを行うと'wordPress'が'WordPress'に変換されるためphpcs:ignoreを指定(上の行も、この行のコメントが書き換えられないように指定)
		$wpdb = new wpdb( 'root', 'password', 'wordpress', $host ); // phpcs:ignore
		assert( $wpdb->is_mysql === true );
		assert( $wpdb->charset === 'utf8mb4' );

		return $wpdb;
	}
}
