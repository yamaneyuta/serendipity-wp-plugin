<?php

declare(strict_types=1);

namespace Cornix\Serendipity\Core;

use Cornix\Serendipity\Core\Hooks\Activation;
use Cornix\Serendipity\Core\Hooks\ContentFilter;
use Cornix\Serendipity\Core\Hooks\PluginUpdated;
use Cornix\Serendipity\Core\Hooks\Cron;
use Cornix\Serendipity\Core\Hooks\ExceptionHandler;
use Cornix\Serendipity\Core\Hooks\RestApi\AdminApi;
use Cornix\Serendipity\Core\Hooks\RestApi\CustomNonce;
use Cornix\Serendipity\Core\Hooks\RestApi\DevelopmentApi;
use Cornix\Serendipity\Core\Hooks\RestApi\EditorApi;
use Cornix\Serendipity\Core\Hooks\RestApi\EveryoneApi;
use Cornix\Serendipity\Core\Hooks\RestApi\NoVersionApi;
use Cornix\Serendipity\Core\Hooks\RestApi\ViewApi;
use Cornix\Serendipity\Core\Hooks\Screen\Admin;
use Cornix\Serendipity\Core\Hooks\Screen\Block;
use Cornix\Serendipity\Core\Hooks\Screen\View;

class EntryPoint {

	public function __construct( $plugin_main_file ) {
		// 例外が発生したときにログを残すhookを登録
		new ExceptionHandler();

		// プラグインのメインファイルを登録し、プラグインのバージョン等を取得できるようにする
		PluginMainFile::initialize( $plugin_main_file );

		// プラグインがアクティブになったときに行うhookを登録
		new Activation();

		// プラグインがアップデートされた時に行うhookを登録
		new PluginUpdated();

		// wp_cronの処理を登録
		new Cron();

		// 投稿画面やfeedで投稿内容をフィルタするhookを登録
		new ContentFilter();

		// REST APIのnonceをカスタマイズするhookを登録
		new CustomNonce();
		// REST API登録
		new EveryoneApi();
		// バージョンを含まないREST API登録
		new NoVersionApi();
		// 管理者向けREST API登録
		new AdminApi();
		// 投稿者向けREST API登録
		new EditorApi();
		// 表示用のREST API登録
		new ViewApi();
		// 開発用のREST API登録
		new DevelopmentApi();

		// 管理画面用hook登録
		new Admin();

		// ブロック用hook登録
		new Block();

		// ビュー用hook登録
		new View();
	}
}
