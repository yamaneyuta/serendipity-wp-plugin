<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Config;

use Cornix\Serendipity\Core\Constants\ChainID;
use Cornix\Serendipity\Core\Constants\NetworkCategoryID;

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
	 * Appコントラクトのアドレス一覧
	 * (このプラグインを導入したユーザーが利用するコントラクトアドレス)
	 *
	 * @var array<int,string>
	 */
	public const APP_CONTRACT_ADDRESSES = array();

	/**
	 * 開発環境用のAppコントラクトアドレス
	 * (開発者が利用するコントラクトアドレス)
	 *
	 * @var array<int,string>
	 */
	public const DEV_APP_CONTRACT_ADDRESSES = array(
		ChainID::PRIVATENET_L1  => '0x5FbDB2315678afecb367f032d93F642f64180aa3',
		ChainID::PRIVATENET_L2  => '0xe7f1725E7734CE288F8367e1Bb143E90bb3F0512',
		ChainID::SEPOLIA        => '0x6e98081f56608E3a9414823239f65c0e6399561d',
		ChainID::SONEIUM_MINATO => '0x6a9214D8264C00d884225542d3af47cf5De2049f',
	);

	/**
	 * ネットワークカテゴリの定義
	 * key: ChainID, value: NetworkCategoryID
	 *
	 * @var array<int,int>
	 */
	public const NETWORK_CATEGORIES = array(
		ChainID::ETH_MAINNET            => NetworkCategoryID::MAINNET,
		ChainID::POLYGON_ZK_EVM         => NetworkCategoryID::MAINNET,
		ChainID::SEPOLIA                => NetworkCategoryID::TESTNET,
		ChainID::POLYGON_ZK_EVM_CARDONA => NetworkCategoryID::TESTNET,
		ChainID::SONEIUM_MINATO         => NetworkCategoryID::TESTNET,
		ChainID::PRIVATENET_L1          => NetworkCategoryID::PRIVATENET,
		ChainID::PRIVATENET_L2          => NetworkCategoryID::PRIVATENET,
	);
}
