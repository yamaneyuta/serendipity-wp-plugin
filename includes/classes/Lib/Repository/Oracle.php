<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Definition\Oracle\OracleDefinitionBase;
use Cornix\Serendipity\Core\Lib\Repository\Definition\Oracle\OracleEthMainnetDefinition;
use Cornix\Serendipity\Core\Lib\Repository\RpcURL;
use Cornix\Serendipity\Core\Types\SymbolPair;

class Oracle {

	public function __construct() {
		$this->oracle_defs = array(
			new OracleEthMainnetDefinition(),
		);
		$this->rpc_url     = new RpcURL();
	}
	/** @var OracleDefinitionBase[] */
	private array $oracle_defs;

	private RpcURL $rpc_url;

	/**
	 * 指定したチェーンに接続可能かどうか(RPC URLが取得できるかどうか)を返します。
	 */
	private function isConnectable( int $chain_ID ): bool {
		return $this->rpc_url->isConnectable( $chain_ID );
	}

	/**
	 * 指定した通貨ペアのOracleがデプロイされている接続可能なチェーンID一覧を取得します。
	 *
	 * @return int[]
	 */
	public function connectableChainIDs( SymbolPair $symbol_pair ): array {
		$chain_IDs = array();
		foreach ( $this->oracle_defs as $oracle_def ) {
			$chain_ID = $oracle_def->chainID();
			if ( ! $this->isConnectable( $chain_ID ) ) {
				continue;
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

	/**
	 * レートを取得可能な法定通貨シンボル一覧を取得します。
	 *
	 * @return string[]
	 */
	public function connectableFiatSymbols(): array {
		$result = array();
		foreach ( $this->oracle_defs as $oracle_def ) {
			if ( ! $this->isConnectable( $oracle_def->chainID() ) ) {
				continue;
			}
			$result = array_merge( $result, $oracle_def->fiatSymbols() );
		}

		// 重複を削除
		return array_values( array_unique( $result ) );
	}
}
