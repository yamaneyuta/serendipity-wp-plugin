<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\OracleData;
use Cornix\Serendipity\Core\Types\NetworkCategory;

class SellableSymbols {

	/**
	 * 販売価格として設定可能な通貨シンボル一覧を取得します。
	 *
	 * @return string[]
	 */
	public function get( NetworkCategory $network_category ): array {
		// レート変換可能な通貨シンボル一覧を取得
		$oracle_symbols = ( new OracleData() )->getSymbols( $network_category );
		// USDを追加
		$oracle_symbols[] = 'USD';

		return $oracle_symbols;
	}
}
