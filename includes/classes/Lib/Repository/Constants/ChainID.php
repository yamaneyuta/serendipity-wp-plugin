<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository\Constants;

final class ChainID {
	// ==================== Mainnet ====================
	/** イーサリアムメインネット(L1) */
	public const ETH_MAINNET = 1;


	/** Polygon zkEVM(L2/mainnet) */
	public const POLYGON_ZK_EVM = 1101; // 0x44d

	// ==================== Testnet ====================
	/** イーサリアムSepoliaテストネット(L1) */
	public const SEPOLIA = 11155111;    // 0xaa36a7


	/** Polygon zkEVMテストネット(L2/Sepolia) */
	public const POLYGON_ZK_EVM_CARDONA = 2442; // 0x98a

	/** Soneiumテストネット(L2/Sepolia) */
	public const SONEIUM_MINATO = 1946; // 0x79a

	// ==================== Privatenet ====================
	/** PrivatenetL1に位置付けられたチェーンID */
	public const PRIVATENET_L1 = 31337; // 0x7a69


	/** PrivatenetL2に位置付けられたチェーンID(L2) ※実際はロールアップを行っていない、単に独立したネットワーク */
	public const PRIVATENET_L2 = 1337;  // 0x539
}
