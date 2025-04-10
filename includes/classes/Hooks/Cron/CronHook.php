<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Hooks\Cron;

use Cornix\Serendipity\Core\Lib\Crawler\AppContractCrawler;
use Cornix\Serendipity\Core\Lib\Logger\Logger;
use Cornix\Serendipity\Core\Lib\Repository\AppContract;
use Cornix\Serendipity\Core\Lib\Repository\BlockNumberActiveSince;
use Cornix\Serendipity\Core\Lib\Repository\ChainData;
use Cornix\Serendipity\Core\Lib\Repository\CrawledBlockNumber;
use Cornix\Serendipity\Core\Lib\Repository\Name\CronActionName;
use Cornix\Serendipity\Core\Lib\Repository\PluginInfo;
use Cornix\Serendipity\Core\Lib\Repository\RPC;
use Cornix\Serendipity\Core\Lib\Repository\Settings\Config;
use Cornix\Serendipity\Core\Lib\Repository\Settings\DefaultValue;
use Cornix\Serendipity\Core\Lib\Web3\BlockchainClientFactory;

/**
 * wp_cronを利用した処理を登録するクラス。
 *
 * Memo:
 * `wp_schedule_event`では、デフォルトで1時間に1回が一番短いサイクル。
 * `cron_schedules`フィルタで追加は可能だが、他プラグインとの競合等を考慮し`wp_schedule_single_event`を毎回登録する方法を採用。
 */
class CronHook {

	public function register(): void {
		// コントラクトのイベントログをクロールするCronを登録
		( new AppContractCrawlCron() )->register();
	}
}

/**
 * AppコントラクトのログをクロールするCronを登録するクラス。
 */
class AppContractCrawlCron {
	public function register(): void {
		// Cronアクション名を取得
		$action_name = CronActionName::appContractCrawl();

		// Appコントラクトのログをクロールするアクションを追加
		add_action( $action_name, array( $this, 'execute' ) );

		// プラグインが無効化された時に登録したアクションを削除
		register_deactivation_hook(
			( new PluginInfo() )->mainFilePath(),
			function () use ( $action_name ) {
				wp_clear_scheduled_hook( $action_name );
			}
		);

		// Cronアクションを登録
		$this->registerSchedule( $action_name );
	}

	/**
	 * Cronアクションを登録します。
	 */
	private function registerSchedule( string $action_name ): void {
		//
		// `wp_schedule_single_event`は、同一のアクション名、同一の引数の場合、登録できる時間に制限がある。
		// => https://developer.wordpress.org/reference/functions/wp_schedule_single_event/
		// > Note that scheduling an event to occur within 10 minutes of an existing event with the same action hook
		// > will be ignored unless you pass unique $args values for each scheduled event.
		// これは、予約を2つ以上登録する際の制限。
		// `wp_next_scheduled`でチェックして存在しない場合に登録する方法であれば、10分の制限は受けない。(30秒ごとに実行、のようなことも可能)
		//

		// 予約がされていない場合のみ登録
		if ( false === wp_next_scheduled( $action_name ) ) {
			$next_time = time() + Config::CRON_INTERVAL_APP_CONTRACT_CRAWL; // 次回の実行時刻

			$success = wp_schedule_single_event( $next_time, $action_name );
			assert( $success === true, '[28D837C0] wp_schedule_single_event failed. ' . var_export( $success, true ) );
		}
	}

	public function execute(): void {
		( new AppContractCrawlCronProcedure() )->execute( 'latest' );
	}
}

class AppContractCrawlCronProcedure {
	public function execute( string $block_tag ): void {
		global $wpdb;
		$crawler = new AppContractCrawler( $wpdb );

		// クロール対象のチェーンID一覧を取得
		$crawlable_chain_ids = ( new AppContractCrawlableChainIDs() )->get();

		// 現時点でクロール対象となる終了ブロック番号を取得(ここまでクロールする)
		// (ループ中に取得し直すとブロック番号が増えて終了しない可能性が出てくるため、先に取得しておく)
		$end_block_number_array = array();
		foreach ( $crawlable_chain_ids as $chain_ID ) {
			$end_block_number_array[ $chain_ID ] = ( new BlockchainClientFactory() )->create( $chain_ID )->getBlockNumber( $block_tag );
		}

		$crawl_failed_chain_ids = array(); // クロールに失敗したチェーンID一覧

		// 各チェーンでクロールを実行
		// ※ チェーンA(未完了) -> チェーンB(完了) -> チェーンC(完了) -> チェーンA(完了) のように、未完了のチェーンがある場合は再度クロールを行う
		// 　 このようなループにすることで、特定のチェーンへのアクセス間隔が空き、リクエスト超過のリスクを減らすことが期待できる
		$is_continue_crawling = false; // クロールを継続するかどうかのフラグ(最後のブロック番号まで取得できないチェーンが存在する場合にtrue)
		do {
			$is_continue_crawling = false; // ループ継続フラグをリセット

			foreach ( $crawlable_chain_ids as $chain_ID ) {
				if ( in_array( $chain_ID, $crawl_failed_chain_ids ) ) {
					// クロールに失敗したチェーンIDはスキップ
					continue;
				}

				$blockchain = ( new BlockchainClientFactory() )->create( $chain_ID );

				// チェーンの最後にクロールしたブロック番号を取得
				$last_crawled_block = ( new CrawledBlockNumber() )->get( $chain_ID, $block_tag );
				// クロールが未実行の場合はアクティブになったブロック番号から開始
				if ( is_null( $last_crawled_block ) ) {
					$last_crawled_block = ( new BlockNumberActiveSince() )->get( $chain_ID );
				}
				assert( ! is_null( $last_crawled_block ), '[65C6AECC] last_crawled_block_hex is null. - chain_ID: ' . $chain_ID );

				// クロール開始ブロックは、最後にクロールしたブロック番号+1
				$from_block_number = $last_crawled_block->add( 1 );

				// 取得するブロック数の最大値
				// TODO: RPC URLに紐づいた最大取得ブロック数を取得するようにする
				$block_range = ( new DefaultValue() )->getLogsMaxRange( $chain_ID );

				// クロール終了ブロック番号を計算
				$to_block_number = $from_block_number->add( $block_range );

				// from >= to の場合は取得するログが存在しないため、クロールをスキップ(次のチェーンへ)
				if ( $from_block_number->compare( $to_block_number ) >= 0 ) {
					continue;
				}

				// クロール終了ブロック番号が最終ブロック番号を超える場合、最終ブロック番号に合わせる
				if ( $to_block_number->compare( $end_block_number_array[ $chain_ID ] ) > 0 ) {
					$to_block_number = $end_block_number_array[ $chain_ID ];
				}

				// クロール終了ブロック番号が最終ブロック番号に達していない場合、次回もクロールを継続
				if ( $to_block_number->compare( $end_block_number_array[ $chain_ID ] ) < 0 ) {
					$is_continue_crawling = true;   // 最後までクロールが完了しないため、次回もクロールを継続
				}

				try {
					// クロール実行
					$crawler->crawl( $chain_ID, $from_block_number, $to_block_number );
					// クロールしたブロック番号を保存
					$ret = ( new CrawledBlockNumber() )->set( $chain_ID, $block_tag, $to_block_number );
					assert( $ret === true, '[2DA97333] CrawledBlock::set failed. - chain_ID: ' . $chain_ID );
				} catch ( \Throwable $e ) {
					// クロールに失敗したチェーンIDを記録
					$crawl_failed_chain_ids[] = $chain_ID;
					Logger::error( $e );
					// 他チェーンのクロールを実施するため、ここでは再スローしない
				}
			}
		} while ( $is_continue_crawling );
	}
}

/**
 * Appコントラクトのイベント取得対象となるチェーンID一覧を取得するクラス。
 */
class AppContractCrawlableChainIDs {

	/**
	 * Appコントラクトのイベント取得を行うチェーンID一覧を取得します。
	 *
	 * 条件:
	 * - コントラクトがデプロイされている
	 * - チェーンに接続可能
	 * - 請求書を発行したことがある
	 *
	 * @return int[]
	 */
	public function get(): array {
		// すべてのチェーンIDを取得
		$all_chain_IDs = ( new ChainData() )->allIDs();

		// RPC URLが取得可能かつアプリケーション用コントラクトアドレスが取得可能なチェーンに絞り込み
		$rpc                   = new RPC();
		$app_contract          = new AppContract();
		$connectable_chain_ids = array_filter( $all_chain_IDs, fn( $chain_id ) => $rpc->isUrlRegistered( $chain_id ) && ! is_null( $app_contract->get( $chain_id ) ) );

		// 取引が開始された(=請求書を発行した)ブロックが存在するチェーンに絞り込み
		$active_since = new BlockNumberActiveSince();
		// TODO: BlockNumberActiveSince::existsメソッドを追加し、それを利用するように変更
		$active_chain_ids = array_filter( $connectable_chain_ids, fn( $chain_id ) => ! is_null( $active_since->get( $chain_id ) ) );

		return array_values( $active_chain_ids );
	}
}
