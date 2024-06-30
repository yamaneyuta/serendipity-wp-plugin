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
		if ( $host === $GLOBALS['wpdb']->dbhost ) {
			return $GLOBALS['wpdb'];
		}

		// phpcsでフォーマットを行うと'WordPress'が'WordPress'に変換されるためphpcs:ignoreを指定
		$wpdb = new wpdb( 'root', 'password', 'wordpress', $host ); // phpcs:ignore
		assert( strpos( $host, 'mysql' ) !== false || strpos( $host, 'mariadb' ) !== false );
		$wpdb->is_mysql = true;
		$wpdb->charset  = 'utf8mb4';

		return $wpdb;
	}
}
