<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Config;

/**
 * システム固定の設定値を取得するためのクラス
 */
class Config {
	/**
	 * レートの一時データの有効期限(秒)
	 */
	public const RATE_TRANSIENT_EXPIRATION = 60 * 10; // 10分

	/**
	 * ブロックチェーンへのリクエストのタイムアウト(秒)
	 */
	public const BLOCKCHAIN_REQUEST_TIMEOUT = 10;

	/**
	 * ブロックチェーンへのリクエストのリトライ間隔(ミリ秒)
	 */
	public const BLOCKCHAIN_REQUEST_RETRY_INTERVALS_MS = array( 1000, 2000, 4000 );

	/**
	 * Appコントラクトのクロール処理を行うCronの間隔(秒)
	 */
	public const CRON_INTERVAL_APP_CONTRACT_CRAWL = 60 * 15; // 15分
}
