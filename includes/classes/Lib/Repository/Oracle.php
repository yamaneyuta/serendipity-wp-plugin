<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Database\Schema\OracleTable;
use Cornix\Serendipity\Core\Types\SymbolPair;

class Oracle {

	/**
	 * 指定した通貨ペアのOracleがデプロイされている接続可能なチェーンID一覧を取得します。
	 *
	 * @return int[]
	 */
	public function connectableChainIDs( SymbolPair $symbol_pair ): array {
		// oracleテーブルに登録されている情報から、baseとquoteが一致するもののチェーンID一覧を取得
		$chain_IDs = array_map(
			fn( $oracle ) => $oracle->chainID(),
			( new OracleTable() )->select( null, null, $symbol_pair->base(), $symbol_pair->quote() )
		);

		// 接続可能(RPC URLが設定済み)なチェーンIDに絞り込み
		$chain_IDs = array_filter( $chain_IDs, fn( $chain_ID ) => ( new RpcURL() )->isRegistered( $chain_ID ) );

		// 重複を削除し、インデックスを振り直した配列を返す
		return array_values( array_unique( $chain_IDs ) );
	}

	/**
	 * 指定したチェーン、通貨ペアのOracleコントラクトのアドレスを取得します。
	 */
	public function address( int $chain_ID, SymbolPair $symbol_pair ): ?string {
		$oracles = ( new OracleTable() )->select( $chain_ID, null, $symbol_pair->base(), $symbol_pair->quote() );
		assert( count( $oracles ) <= 1 );

		return count( $oracles ) === 0 ? null : $oracles[0]->oracleAddress();
	}
}
