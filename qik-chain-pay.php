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

use Cornix\Serendipity\Core\Infrastructure\DI\ContainerDefinitions;
use Cornix\Serendipity\Core\Lib\Rest\RestProperty;
use Cornix\Serendipity\Core\Presentation\AdminPageHook;
use Cornix\Serendipity\Core\Presentation\ContentIoHook;
use Cornix\Serendipity\Core\Presentation\CronHook;
use Cornix\Serendipity\Core\Presentation\GraphQLHook;
use Cornix\Serendipity\Core\Presentation\PluginUpdateHook;
use Cornix\Serendipity\Core\Presentation\PostEditHook;
use Cornix\Serendipity\Core\Presentation\ViewPageHook;
use DI\ContainerBuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ライブラリ読み込み
require_once __DIR__ . '/includes/vendor/autoload.php';

$main = function () {
	$containerBuilder = new ContainerBuilder();
	$containerBuilder->addDefinitions( ContainerDefinitions::getDefinitions() );
	$container = $containerBuilder->build();

	// プラグインの初期化
	$container->get( PluginUpdateHook::class )->register();

	// Cronの登録
	$container->get( CronHook::class )->register();

	// GraphQLのAPI登録
	( new GraphQLHook( $container->get( RestProperty::class ), $container ) )->register();

	// 管理画面
	$container->get( AdminPageHook::class )->register();
	// 投稿(新規/編集)画面
	$container->get( PostEditHook::class )->register();
	// 投稿表示画面
	$container->get( ViewPageHook::class )->register();

	// 投稿を保存または取得する時のフィルタ処理
	$container->get( ContentIoHook::class )->register();
};

$main();
