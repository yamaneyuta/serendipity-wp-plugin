<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Constant;

use Cornix\Serendipity\Core\Constant\ChainIdValue;
use Cornix\Serendipity\Core\Constant\NetworkCategoryID;

/**
 * システム固定の設定値を取得するためのクラス
 */
class Config {

	/**
	 * このプラグインのルートディレクトリ
	 * (エントリファイルが存在するディレクトリのパス)
	 */
	public const ROOT_DIR = __DIR__ . '/../../..';

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

	/**
	 * 最小のブロック待機数
	 * ブロックにトランザクションが取り込まれた時点で1とカウントする
	 */
	public const MIN_CONFIRMATIONS = 1; // 【変更不可】

	/**
	 * ネットワークカテゴリの定義
	 * key: ChainID, value: NetworkCategoryID
	 *
	 * @var array<int,int>
	 */
	public const NETWORK_CATEGORIES = array(
		ChainIdValue::ETH_MAINNET            => NetworkCategoryID::MAINNET,
		ChainIdValue::POLYGON_ZK_EVM         => NetworkCategoryID::MAINNET,
		ChainIdValue::SEPOLIA                => NetworkCategoryID::TESTNET,
		ChainIdValue::POLYGON_ZK_EVM_CARDONA => NetworkCategoryID::TESTNET,
		ChainIdValue::SONEIUM_MINATO         => NetworkCategoryID::TESTNET,
		ChainIdValue::PRIVATENET_L1          => NetworkCategoryID::PRIVATENET,
		ChainIdValue::PRIVATENET_L2          => NetworkCategoryID::PRIVATENET,
	);
}
