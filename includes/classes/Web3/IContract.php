<?php
declare(strict_types=1);
namespace Cornix\Serendipity\Core\Web3;

use Cornix\Serendipity\Core\Web3\DataType\OracleLatestData;

/**
 * コントラクトからのデータをキャッシュするクラスの定義
 */
interface IContract {
	public function getChainId(): int;

	/**
	 * @param string[] $symbols
	 * @return OracleLatestData[]
	 */
	public function getOracleLatestData( array $symbols ): array;

	/**
	 * @return array{symbols:string[],isPausedSymbols:bool[],blockNumber:string,chainId:int}
	 */
	public function getSellableSymbolsInfo(): array;

	/**
	 * @return array{symbols:string[],decimals:int[],tokenTypes:int[],isPaused:bool[],blockNumber:string,chainId:int}
	 */
	public function getPayableSymbolsInfo(): array;
}
