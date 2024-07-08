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
	public function get(): array {
		error_log( '{DF2EDA7F-484D-4631-8AD3-924A6BAB1D79}' );
		$chain_id = 1;  // TODO: 販売しようとしているネットワークに対するチェーンIDを取得する

		// レート変換可能な通貨シンボル一覧を取得
		$oracle_symbols = ( new OracleData() )->getSymbols( $chain_id );
		// USDを追加
		$oracle_symbols[] = 'USD';

		return $oracle_symbols;
	}
}
