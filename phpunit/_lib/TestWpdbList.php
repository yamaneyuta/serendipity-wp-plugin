<?php

class TestWpdbList {

	/**
	 *
	 * @return wpdb[]
	 */
	public function get(): array {
		/** @var wpdb[] $ret */
		$ret = array();

		// `wp-env`が起動した`tests-mysql`に接続するwpdb
		$ret[] = $GLOBALS['wpdb'];

		// 別途立ち上げたデータベースに接続するwpdb
		foreach ( $this->extDatabaseHosts() as $host ) {
			$ret[] = $this->createWpdb( $host );
		}

		return $ret;
	}

	private function extDatabaseHosts(): array {
		// SQL発行テスト用に立ち上げたデータベースのホスト名一覧
		// ※ `wp-env`が立ち上げた`tests-mysql`は含まない
		return array(
			'mysql-phpunit-oldest',
			'mysql-phpunit-latest',
			'mariadb-phpunit-oldest',
			'mariadb-phpunit-latest',
		);
	}

	private function createWpdb( string $host ): wpdb {
		assert( $host !== $GLOBALS['wpdb']->dbhost );

		// phpcsでフォーマットを行うと'WordPress'が'WordPress'に変換されるためphpcs:ignoreを指定
		$wpdb = new wpdb( 'root', 'password', 'wordpress', $host ); // phpcs:ignore
		assert( strpos( $host, 'mysql' ) !== false || strpos( $host, 'mariadb' ) !== false );
		$wpdb->is_mysql = true;
		$wpdb->charset  = 'utf8mb4';

		return $wpdb;
	}
}
