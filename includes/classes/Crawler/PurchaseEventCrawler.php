<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Crawler;

use Cornix\Serendipity\Core\Web3\Contract;
use Cornix\Serendipity\Core\Database\Database;
use Cornix\Serendipity\Core\Logger\Logger;
use Cornix\Serendipity\Core\Settings\Settings;
use Cornix\Serendipity\Core\Utils\Calculator;
use Cornix\Serendipity\Core\Utils\Constants;
use Cornix\Serendipity\Core\Web3\ChainId;
use Cornix\Serendipity\Core\Web3\Rpc;

/**
 * 購入時のイベントを取得するクラス
 *
 * ※ 現在のブロック番号をキャッシュするため、時間経過で再度新しいブロック番号が必要な場合は新しいインスタンスを生成すること
 */
class PurchaseEventCrawler {

	/** @var array<int,string> チェーンIDに対する現在のブロック番号(hex)のキャッシュ */
	private $current_block_numbers_hex_cache = array();

	public function __construct() {
	}

	/**
	 *
	 * @param callable():bool $should_continue_crawling
	 * @return void
	 */
	public function execute( callable $should_continue_crawling ): void {

		// 稼働中のネットワークでRPC URLが登録されているチェーンID一覧を取得
		$chain_ids = Settings::getActiveNetworksRegisteredChainIds();

		// $should_continue_crawlingがtrueを返す場合でも1回は実行する
		do {
			$is_all_crawled = true; // すべてクローズ済みかどうかのフラグ(まだ残っているチェーンがある場合、falseになる)

			// 各チェーンIDに対してクロールを実行
			foreach ( $chain_ids as $chain_id ) {

				// Reorgが発生している場合はReorgに対応する処理を実行
				if ( $this->isReorg( $chain_id ) ) {
					$this->onReorg( $chain_id );
				}

				// クロールしていないブロックがある場合はクロールを実施
				if ( $this->isCrawlNeeded( $chain_id ) ) {
					$this->crawl( $chain_id );
				}

				// まだクロールしていないチェーンがある場合はフラグをfalseにする
				$is_all_crawled = $is_all_crawled && $this->isCrawlNeeded( $chain_id );

				// クロールを継続する条件が満たされていない場合は処理抜け
				if ( false === $should_continue_crawling() ) {
					break;
				}
			}

			// まだクロールすべきチェーンが存在し、クロールを継続する条件の場合は待機後、再度クロールを実行
			if ( false === $is_all_crawled && $should_continue_crawling() ) {
				sleep( 1 );
				continue;
			} else {
				// クロールを継続しない場合は処理抜け
				break;
			}
		} while ( false );
	}

	/** クロールが必要かどうかを返す */
	private function isCrawlNeeded( int $chain_id ): bool {
		// 最後にクロールを行ったブロック番号を取得
		$last_crawl_block_number_hex = Database::getLastCrawlPurchasedEventLogBlockNumber( $chain_id );

		// 現在のブロック番号を16進数の文字列で取得
		$current_block_number_hex = $this->getCurrentBlockNumber( $chain_id );

		// 最後にクロールを行ったブロック番号と現在のブロック番号が異なる場合はクロールが必要
		return ( 0 < Calculator::compare( $current_block_number_hex, $last_crawl_block_number_hex ) );
	}

	private function crawl( int $chain_id ): void {

		$contract = new Contract( $chain_id );

		$start_crawl_block_number_hex = $this->getStartCrawlBlockNumber( $chain_id );
		$last_crawl_block_number_hex  = $this->getLastCrawlBlockNumber( $chain_id );
		$crawl_block_range            = $this->getCrawlBlockRange( $chain_id );

		$from_block_hex = $start_crawl_block_number_hex;

		$to_block_hex = Calculator::add( $from_block_hex, $crawl_block_range );
		if ( 0 < Calculator::compare( $to_block_hex, $last_crawl_block_number_hex ) ) {
			$to_block_hex = $last_crawl_block_number_hex;
		}

		$events = $contract->getPurchaseEventLog( $from_block_hex, $to_block_hex );

		foreach ( $events as $event ) {
			Database::setPurchaseEventLog( $event );
		}

		// 最後にクロールを行ったブロック番号を更新
		Database::setLastCrawlPurchasedEventLogBlockNumber( $chain_id, $to_block_hex );

		return;
	}

	/** クロールを行う最初のブロック番号を取得します。 */
	private function getStartCrawlBlockNumber( int $chain_id ): string {
		// 最後にクロールを行ったブロック番号を取得
		$last_crawl_block_number_hex = Database::getLastCrawlPurchasedEventLogBlockNumber( $chain_id );

		// 次のブロックからクロールを行う
		return Calculator::add( $last_crawl_block_number_hex, 1 );
	}


	/** クロールを行う最後のブロック番号を取得します。 */
	private function getLastCrawlBlockNumber( int $chain_id ): string {

		// 現在のブロック番号を16進数の文字列で取得
		$current_block_number_hex = $this->getCurrentBlockNumber( $chain_id );

		// 待機するブロック数の既定値を取得
		$default = (int) Constants::get( "default.confirmations.$chain_id" );
		// 待機するブロック数を取得(reorg対策)
		$confirmations = Database::getTxConfirmations_old( $chain_id, $default );

		// 待機ブロック数を反映した最終クロールブロック番号を返す
		return Calculator::sub( $current_block_number_hex, ( $confirmations - 1 ) );
	}


	/** 指定したチェーンの現在のブロック番号(hex)を取得します */
	private function getCurrentBlockNumber( int $chain_id ): string {
		// キャッシュに値がない場合はRPCから取得
		if ( ! array_key_exists( $chain_id, $this->current_block_numbers_hex_cache ) ) {
			$this->current_block_numbers_hex_cache[ $chain_id ] = Rpc::getBlockNumber( Database::getRpcUrl( $chain_id ) );
		}

		return $this->current_block_numbers_hex_cache[ $chain_id ];
	}

	/** 指定したチェーンの1回あたりのログ取得範囲を返します */
	private function getCrawlBlockRange( int $chain_id ): int {
		return Database::getLogBlockRange( $chain_id );
	}

	/**
	 * Reorgが発生しているかどうかを返します。
	 * 現在のブロック番号がクロール済みブロック番号よりも小さい場合、Reorgが発生していると判定します。
	 *
	 * @param int $chain_id
	 * @return bool
	 */
	private function isReorg( int $chain_id ): bool {
		// 現在のブロック番号を16進数の文字列で取得
		$current_block_number_hex = $this->getCurrentBlockNumber( $chain_id );

		// 最後にクロールを行ったブロック番号を取得
		$last_crawl_block_number_hex = Database::getLastCrawlPurchasedEventLogBlockNumber( $chain_id );

		// 現在のブロック番号がクロール済みブロック番号よりも小さい場合、Reorgが発生していると判定
		$is_reorg = ( 0 > Calculator::compare( $current_block_number_hex, $last_crawl_block_number_hex ) );

		if ( $is_reorg ) {
			Logger::info( "[CEFFCC4E] Reorg detected. Chain ID: $chain_id, Current block number: $current_block_number_hex, Last crawl block number: $last_crawl_block_number_hex" );
		}

		return $is_reorg;
	}

	/**
	 * Reorgが発生した場合の処理を行います。
	 *
	 * @param int $chain_id
	 * @return void
	 */
	private function onReorg( int $chain_id ): void {
		if ( ChainId::isMainnet( $chain_id ) || ChainId::isTestnet( $chain_id ) ) {
			// TODO: テストネットやメインネットでReorgが発生した場合の処理を実装
			Logger::warn( "[01832350] Not implemented. Chain ID: $chain_id" );
		} elseif ( ChainId::isPrivatenet( $chain_id ) ) {
			// プライベートネットの場合
			// => プライベートネットの場合、ブロック番号が巻き戻るのは再起動した時。
			// 過去のトランザクションが消失するので、購入履歴のログを全て削除する。

			// クロールするチェーンの最初の場合は、Loggerによって記録されたログを削除
			if ( 0 === array_search( $chain_id, Settings::getActiveNetworksRegisteredChainIds() ) ) {
				Database::deleteAllLogs();
				Logger::info( '[2DEAAFA2] All logs have been deleted.' );
			}

			// 過去の購入ログをすべて削除
			Database::deletePurchaseEventLogs( array( $chain_id ) );
			Logger::info( "[129E182B] All purchase event logs have been deleted. Chain ID: $chain_id" );

			// TODO: 販売価格の履歴も最新のものだけ残して削除する処理を追加

			// クロール済みブロック番号を0x00にリセット
			Database::setLastCrawlPurchasedEventLogBlockNumber( $chain_id, '0x00' );
			Logger::info( "[4D54783D] Last crawl block number has been reset. Chain ID: $chain_id" );

		}
	}
}
