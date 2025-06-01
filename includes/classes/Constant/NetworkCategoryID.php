<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Constant;

final class NetworkCategoryID {
	/** メインネット(Ethereumメインネット、Polygonメインネット等) */
	public const MAINNET = 1;
	/** テストネット(Ethereum Sepolia等) */
	public const TESTNET = 2;
	/** プライベートネット(Ganache、Hardhat等) */
	public const PRIVATENET = 3;
}
