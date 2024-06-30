<?php
declare(strict_types=1);

class TestDBHosts {

	/**
	 * テスト対象のデータベースホスト名一覧を取得します。
	 *
	 * @return string[]
	 */
	public function get(): array {
		// テスト対象は`wp-env`が起動した`tests-mysql`とdockerで起動したデータベース
		return array(
			$GLOBALS['wpdb']->dbhost,
			...$this->extDatabaseHosts(),
		);
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
}
