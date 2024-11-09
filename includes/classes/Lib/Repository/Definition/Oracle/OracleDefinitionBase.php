<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository\Definition\Oracle;

use Cornix\Serendipity\Core\Types\SymbolPair;

abstract class OracleDefinitionBase {
	/**
	 * このクラスが管理するコントラクトがデプロイされているチェーンIDを取得します。
	 */
	abstract public function chainID(): int;

	/**
	 * 指定した通貨ペアに対応するOracleコントラクトのアドレスを取得します。
	 */
	abstract public function getAddress( SymbolPair $symbol_pair ): ?string;

	/**
	 * 法定通貨シンボル一覧を取得します。
	 *
	 * @return string[]
	 */
	abstract public function fiatSymbols(): array;
}
