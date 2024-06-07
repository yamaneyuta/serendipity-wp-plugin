<?php

// このファイルは`WordPressコンテナ(test-cli)`の中で実行されます。
// - __DIR__: /var/www/html/wp-content/plugins/workspaces/phpunit

// `yoast/wp-test-utils`に記載の実装例を参考に以下内容を記載。
// https://github.com/Yoast/wp-test-utils#using-the-bootstrap-utilities

use Yoast\WPTestUtils\WPIntegration;

// `.phpunit/composer.json`でインストールした、テスト用のユーティリティを読み込む
require_once dirname( __DIR__ ) . '/.phpunit/vendor/yoast/wp-test-utils/src/WPIntegration/bootstrap-functions.php';

// `tests_add_filter`関数を使えるようにする
// ※ WPIntegration\get_path_to_wp_test_dir() => `/wordpress-phpunit/`
require_once WPIntegration\get_path_to_wp_test_dir() . 'includes/functions.php';

// 手動でプラグインをロードするコールバック
function _manually_load_plugin() {
	require_once dirname( __DIR__ ) . '/todo-list.php';
}

/** @disregard P1010 */
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

/*
 * Bootstrap WordPress. This will also load the Composer autoload file, the PHPUnit Polyfills
 * and the custom autoloader for the TestCase and the mock object classes.
 */
WPIntegration\bootstrap_it();
