<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository;

use Cornix\Serendipity\Core\Entity\Chain;
use Cornix\Serendipity\Core\Lib\Database\Table\OracleTable;
use Cornix\Serendipity\Core\ValueObject\NetworkCategory;

class SellableSymbols {

	/**
	 * 販売価格として設定可能な通貨シンボル一覧を取得します。
	 *
	 * @return string[]
	 */
	public function get( NetworkCategory $network_category ): array {
		// 方針: Oracleテーブルに登録されているbase及びquoteの通貨シンボルは販売可能な通貨シンボルとして扱う。
		// 　　　その上で、現時点で販売価格として設定できるものはRPC URLが設定されているものとする。

		// テーブルに登録されているOracle情報をすべて取得
		$oracles = ( new OracleTable() )->select();

		// 接続可能なoracleに絞り込み
		$oracles = array_filter(
			$oracles,
			fn( $oracle ) => ( new Chain( $oracle->chainID() ) )->connectable()
		);

		// baseとquoteの通貨シンボルを取得
		$symbols = array();
		foreach ( $oracles as $oracle ) {
			$symbols[] = $oracle->baseSymbol();
			$symbols[] = $oracle->quoteSymbol();
		}

		// 重複を削除
		$symbols = array_unique( $symbols );

		// インデックスを振り直した配列を返す
		return array_values( $symbols );
	}
}
