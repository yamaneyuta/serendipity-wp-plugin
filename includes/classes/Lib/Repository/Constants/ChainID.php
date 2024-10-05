<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository\Constants;

final class ChainID {
	// ==================== Mainnet ====================
	/** イーサリアムメインネット */
	public const ETH_MAINNET = 1;

	// ==================== Testnet ====================
	/** イーサリアムSepoliaテストネット */
	public const SEPOLIA = 11155111;

	// ==================== Privatenet ====================
	/** PrivatenetL1に位置付けられたチェーンID */
	public const PRIVATENET_L1 = 31337;
	/** PrivatenetL2に位置付けられたチェーンID */
	public const PRIVATENET_L2 = 1337;
}
