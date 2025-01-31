<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Definition\Oracle\OracleDefinitionBase;
use Cornix\Serendipity\Core\Lib\Repository\Definition\Oracle\OracleEthMainnetDefinition;
use Cornix\Serendipity\Core\Types\SymbolPair;

class Oracle {

	public function __construct() {
		$this->oracle_defs = array(
			new OracleEthMainnetDefinition(),
		);
	}
	/** @var OracleDefinitionBase[] */
	private array $oracle_defs;

	/**
	 * 指定した通貨ペアのOracleがデプロイされている接続可能なチェーンID一覧を取得します。
	 *
	 * @return int[]
	 */
	public function connectableChainIDs( SymbolPair $symbol_pair ): array {
		$chain_IDs = array();
		$rpc_url   = new RpcURL();
		foreach ( $this->oracle_defs as $oracle_def ) {
			$chain_ID = $oracle_def->chainID();
			if ( ! $rpc_url->isRegistered( $chain_ID ) ) {
				continue; // RPC URLが未登録(接続不可)の場合はスキップ
			}

			if ( ! is_null( $oracle_def->getAddress( $symbol_pair ) ) ) {
				$chain_IDs[] = $chain_ID;
			}
		}
		return $chain_IDs;
	}

	/**
	 * 指定したチェーン、通貨ペアのOracleコントラクトのアドレスを取得します。
	 */
	public function address( int $chain_ID, SymbolPair $symbol_pair ): ?string {
		array_filter( $this->oracle_defs, fn( $def ) => $def->chainID() === $chain_ID );
		return count( $this->oracle_defs ) === 0 ? null : array_values( $this->oracle_defs )[0]->getAddress( $symbol_pair );
	}
}
