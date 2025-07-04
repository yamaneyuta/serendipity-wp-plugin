<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Repository;

use Cornix\Serendipity\Core\Infrastructure\Factory\ChainServiceFactory;
use Cornix\Serendipity\Core\Domain\ValueObject\NetworkCategoryID;
use Cornix\Serendipity\Core\Infrastructure\Database\Repository\OracleRepositoryImpl;

class SellableSymbols {

	/**
	 * 販売価格として設定可能な通貨シンボル一覧を取得します。
	 *
	 * @return string[]
	 */
	public function get( NetworkCategoryID $_ ): array {
		// 方針: Oracleテーブルに登録されているbase及びquoteの通貨シンボルは販売可能な通貨シンボルとして扱う。
		// 　　　その上で、現時点で販売価格として設定できるものはRPC URLが設定されているものとする。

		// テーブルに登録されているOracle情報をすべて取得
		$oracles       = ( new OracleRepositoryImpl() )->all();
		$chain_service = ( new ChainServiceFactory() )->create();

		// 接続可能なoracleに絞り込み
		$oracles = array_filter(
			$oracles,
			fn( $oracle ) => $oracle->chain()->connectable()
		);

		// baseとquoteの通貨シンボルを取得
		$symbols = array();
		foreach ( $oracles as $oracle ) {
			$symbols[] = $oracle->symbolPair()->base();
			$symbols[] = $oracle->symbolPair()->quote();
		}

		// 重複を削除
		$symbols = array_unique( $symbols );

		// インデックスを振り直した配列を返す
		return array_values( $symbols );
	}
}
