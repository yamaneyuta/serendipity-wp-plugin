<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\Repository;

use Cornix\Serendipity\Core\Lib\Repository\OracleData;

class SellableSymbols {

	/**
	 * 販売価格として設定可能な通貨シンボル一覧を取得します。
	 *
	 * @return string[]
	 */
	public function get( int $chain_ID ): array {
		// レート変換可能な通貨シンボル一覧を取得
		$oracle_symbols = ( new OracleData() )->getSymbols( $chain_ID );
		// USDを追加
		$oracle_symbols[] = 'USD';

		return $oracle_symbols;
	}
}
