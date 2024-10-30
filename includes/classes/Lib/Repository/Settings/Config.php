<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository\Settings;

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
}
