<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Hooks;

use Cornix\Serendipity\Core\Crawler\PurchaseEventCrawler;
use Cornix\Serendipity\Core\Logger\Logger;
use Cornix\Serendipity\Core\Utils\Constants;

/**
 * wp_cronを利用した処理をまとめたクラス。
 *
 * Memo:
 * `wp_schedule_event`では、デフォルトで1時間に1回が一番短いサイクル。
 * `cron_schedules`フィルタで追加は可能だが、他プラグインとの競合等を考慮し`wp_schedule_single_event`を毎回登録する方法を採用。
 */
class Cron {

	/** コンストラクタ */
	public function __construct() {
		// 購入イベントログのクロール処理を登録
		//
		$crawl_purchased_event_action_name = Constants::get( 'cronActionName.crawlPurchasedEventLog' );
		// 購入イベントログをクロールするアクションを追加
		add_action( $crawl_purchased_event_action_name, array( $this, 'crawlPurchasedEventLog' ) );
		// Cronに登録されていない場合、追加する
		if ( ! wp_next_scheduled( $crawl_purchased_event_action_name ) ) {
			$interval = Constants::get( 'cronIntervalMin.crawlPurchasedEventLog' );
			wp_schedule_single_event( time() + ( $interval * 60 ), $crawl_purchased_event_action_name );
		}
	}

	public function crawlPurchasedEventLog() {
		Logger::info( 'Start ' . __METHOD__ );  // 動作しているか確認するためinfoログを出力
		try {
			// 実行間隔を取得
			$interval = Constants::get( 'cronIntervalMin.crawlPurchasedEventLog' );
			// 次回実行する時間を取得
			$next_time = wp_next_scheduled( Constants::get( 'cronActionName.crawlPurchasedEventLog' ) );
			if ( false === $next_time ) {
				// 次回実行時間が取得できなかったときは現在時刻から実行間隔を加算
				$next_time = time() + ( $interval * 60 );
			}

			( new PurchaseEventCrawler() )->execute(
				function () use ( $next_time ) {
					// 最大で次回実行の30秒前まで実行する
					return time() < $next_time - 30;
				}
			);
		} catch ( \Exception $e ) {
			Logger::error( $e );
			throw $e;
		} finally {
			Logger::info( 'End ' . __METHOD__ );
		}
	}
}
