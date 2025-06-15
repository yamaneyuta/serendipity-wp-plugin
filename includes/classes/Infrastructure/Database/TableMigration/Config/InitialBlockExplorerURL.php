<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Infrastructure\Database\TableMigration\Config;

/** プラグインインストール時に設定されるブロックエクスプローラーURL */
final class InitialBlockExplorerURL {

	// Mainnet
	public const ETH_MAINNET = 'https://etherscan.io';

	// Testnet
	public const SEPOLIA        = 'https://sepolia.etherscan.io';
	public const SONEIUM_MINATO = 'https://soneium-minato.blockscout.com';

	// Privatenet
	public const PRIVATENET_L1 = 'http://localhost:10101';
	public const PRIVATENET_L2 = 'http://localhost:10102';
}
