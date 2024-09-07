<?php
/**
 * Plugin Name:       Todo List
 * Description:       Example static block scaffolded with Create Block tool.
 * Requires at least: 5.4
 * Requires PHP:      7.4
 * Version:           0.8.0
 * Author:            The WordPress Contributors
 * License:           Split License
 * License URI:       ./LICENSE
 * Text Domain:       todo-list
 */

declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ライブラリ読み込み
require_once __DIR__ . '/includes/vendor/autoload.php';

new Cornix\Serendipity\Core\Hooks\EntryPoint( __FILE__ );
