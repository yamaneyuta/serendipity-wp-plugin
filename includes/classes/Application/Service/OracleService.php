<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Application\Service;

use Cornix\Serendipity\Core\Domain\Specification\OraclesFilter;
use Cornix\Serendipity\Core\Infrastructure\Database\Repository\OracleRepository;
use Cornix\Serendipity\Core\Domain\ValueObject\Address;
use Cornix\Serendipity\Core\Domain\ValueObject\SymbolPair;
use Cornix\Serendipity\Core\Domain\ValueObject\ChainID;

class OracleService {

	public function __construct() {
		$this->oracle_repository = new OracleRepository();
	}
	private OracleRepository $oracle_repository;

	/**
	 * 指定した通貨ペアのOracleがデプロイされている接続可能なチェーンID一覧を取得します。
	 *
	 * @return ChainID[]
	 */
	public function connectableChainIDs( SymbolPair $symbol_pair ): array {
		$oracles_filter = ( new OraclesFilter() )
			->bySymbolPair( $symbol_pair )
			->byConnectable();
		$oracles        = $oracles_filter->apply( $this->oracle_repository->all() );

		$chain_IDs = array_map(
			fn( $oracle ) => $oracle->chain()->id()->value(),
			$oracles
		);

		// 重複を削除し、インデックスを振り直した配列を返す
		return array_values( array_map( fn( $id ) => new ChainID( $id ), array_unique( $chain_IDs ) ) );
	}

	/**
	 * 指定したチェーン、通貨ペアのOracleコントラクトのアドレスを取得します。
	 */
	public function address( ChainID $chain_ID, SymbolPair $symbol_pair ): ?Address {
		$oracles_filter = ( new OraclesFilter() )->byChainID( $chain_ID )
			->bySymbolPair( $symbol_pair );
		$oracles        = $oracles_filter->apply( $this->oracle_repository->all() );
		assert( count( $oracles ) <= 1 );

		return 0 === count( $oracles ) ? null : array_values( $oracles )[0]->address();
	}
}
