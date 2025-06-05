<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Service;

use Cornix\Serendipity\Core\Lib\Algorithm\Filter\OraclesFilter;
use Cornix\Serendipity\Core\Repository\OracleRepository;
use Cornix\Serendipity\Core\Service\ChainService;
use Cornix\Serendipity\Core\ValueObject\Address;
use Cornix\Serendipity\Core\ValueObject\SymbolPair;

class OracleService {

	/**
	 * 指定した通貨ペアのOracleがデプロイされている接続可能なチェーンID一覧を取得します。
	 *
	 * @return int[]
	 */
	public function connectableChainIDs( SymbolPair $symbol_pair ): array {
		$oracles_filter = ( new OraclesFilter() )->bySymbolPair( $symbol_pair );
		$oracles        = $oracles_filter->apply( ( new OracleRepository() )->all() );
		// oracleテーブルに登録されている情報から、baseとquoteが一致するもののチェーンID一覧を取得
		$chain_IDs = array_map(
			fn( $oracle ) => $oracle->chainID(),
			$oracles->toArray()
		);

		// 接続可能なチェーンIDに絞り込み
		$chain_IDs = array_filter( $chain_IDs, fn( $chain_ID ) => ( new ChainService( $chain_ID ) )->connectable() );

		// 重複を削除し、インデックスを振り直した配列を返す
		return array_values( array_unique( $chain_IDs ) );
	}

	/**
	 * 指定したチェーン、通貨ペアのOracleコントラクトのアドレスを取得します。
	 */
	public function address( int $chain_ID, SymbolPair $symbol_pair ): ?Address {
		$oracles_filter = ( new OraclesFilter() )->byChainID( $chain_ID )
			->bySymbolPair( $symbol_pair );
		$oracles        = $oracles_filter->apply( ( new OracleRepository() )->all() );
		assert( $oracles->count() <= 1 );

		return $oracles->count() === 0 ? null : array_values( $oracles->toArray() )[0]->oracleAddress();
	}
}
