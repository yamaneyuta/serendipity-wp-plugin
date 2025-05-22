<?php
/**
 * Plugin Name:       Qik Chain Pay
 * Description:       Allows you to implement a paywall using crypto-assets.
 * Requires at least: 5.4
 * Requires PHP:      7.4
 * Version:           0.8.0
 * Author:            yamaneyuta
 * License:           Split License
 * License URI:       ./LICENSE
 * Text Domain:       qik-chain-pay
 * Domain Path:       /languages
 */

// [Header Requirements](https://developer.wordpress.org/plugins/plugin-basics/header-requirements/)

declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ライブラリ読み込み
require_once __DIR__ . '/includes/vendor/autoload.php';

new Cornix\Serendipity\Core\Hooks\EntryPoint( __FILE__ );
