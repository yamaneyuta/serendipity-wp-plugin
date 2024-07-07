<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Features\Repository;

class SellableSymbols {

	/**
	 * 販売価格として設定可能な通貨シンボル一覧を取得します。
	 *
	 * @return string[]
	 */
	public function get(): array {
		// TODO: 販売価格として設定可能な通貨シンボル一覧を返す
		return array( 'USD', 'JPY', 'ETH' );  // ダミーデータ
	}
}
