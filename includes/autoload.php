<?php
declare(strict_types=1);

use Cornix\Serendipity\Core\Tools\DelayInstaller;
use Cornix\Serendipity\Core\Utils\LocalPath;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// このプロジェクト用のクラスを含むライブラリの読み込み。
require_once __DIR__ . '/vendor/autoload.php';


// 後からインストールしたライブラリのautoloadを読み込む。
$delay_installed_autoload_path = LocalPath::getDelayInstalledAutoloadPath();
if ( ! file_exists( $delay_installed_autoload_path ) ) {
	DelayInstaller::execute();
}
require_once $delay_installed_autoload_path;
