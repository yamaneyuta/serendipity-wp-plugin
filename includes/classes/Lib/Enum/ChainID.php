<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Enum;

final class ChainID {
	/** イーサリアムメインネット */
	public const ETH_MAINNET = 1;

	/** Hardhatテスト環境 */
	public const HARDHAT = 31337;

	/** イーサリアムSepoliaテストネット */
	public const SEPOLIA = 11155111;
}
