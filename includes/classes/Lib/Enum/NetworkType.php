<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Enum;

/**
 * ネットワーク種別
 */
final class NetworkType {
	/**
	 * メインネット。本プラグインにおいては、トークンに価値があるネットワークを指します。
	 * 例: Ethereumメインネット、Binance Smart Chainメインネット
	 */
	public const MAINNET = 'MAINNET';

	/**
	 * テストネット。本プラグインにおいては、トークンに価値がないネットワークを指します。
	 * 例: Ethereum Sepoliaテストネット、Binance Smart Chainテストネット
	 */
	public const TESTNET = 'TESTNET';

	/**
	 * プライベートネット。本プラグインにおいては、ローカル環境に構築したネットワークを指します。
	 * 例: Ganache、Hardhat
	 */
	public const PRIVATENET = 'PRIVATENET';
}
