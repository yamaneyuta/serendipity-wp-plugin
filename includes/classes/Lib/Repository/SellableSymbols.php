<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Oracle;
use Cornix\Serendipity\Core\Types\NetworkCategory;

class SellableSymbols {

	/**
	 * 販売価格として設定可能な通貨シンボル一覧を取得します。
	 *
	 * @return string[]
	 */
	public function get( NetworkCategory $network_category ): array {
		// TODO: 引数を削除
		// 法定通貨シンボル一覧を取得
		$oracle_symbols = ( new Oracle() )->connectableFiatSymbols();
		// USDを追加
		$oracle_symbols[] = 'USD';

		// TODO: トークンを販売価格として指定できる設定がされている場合はETH等を追加

		return $oracle_symbols;
	}
}
