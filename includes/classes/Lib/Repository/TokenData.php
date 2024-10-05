<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Repository\Constants\ChainID;

class TokenData {

	private const CHAIN_ID_INDEX = 0;
	private const SYMBOL_INDEX   = 1;
	private const ADDRESS_INDEX  = 2;
	private const DECIMALS_INDEX = 3;

	public function __construct() {
		$mainnet  = ChainID::ETH_MAINNET;
		$private1 = ChainID::PRIVATENET_L1;
		$private2 = ChainID::PRIVATENET_L2;

		$token_data_json = <<<JSON
			{
				"data": [
					[ {$mainnet}, "ETH", "0x0000000000000000000000000000000000000000", 18 ],

					[ {$private1}, "ETH", "0x0000000000000000000000000000000000000000", 18 ],
					[ {$private1}, "TUSD", "0x5FC8d32690cc91D4c39d9d3abcBD16989F875707", 18 ],
					[ {$private1}, "TJPY", "0xa513E6E4b8f2a923D98304ec87F64353C4D5C853", 18 ],

					[ {$private2}, "MATIC", "0x0000000000000000000000000000000000000000", 18 ],
					[ {$private2}, "TUSD", "0x5FC8d32690cc91D4c39d9d3abcBD16989F875707", 18 ],
					[ {$private2}, "TJPY", "0xa513E6E4b8f2a923D98304ec87F64353C4D5C853", 18 ]
				]
			}
		JSON;

		$this->token_data = json_decode( $token_data_json, true )['data'];
	}

	private array $token_data;

	/**
	 * トークンのコントラクトアドレスを取得します。
	 * (ネイティブトークンの場合は`0x0000000000000000000000000000000000000000`)
	 */
	public function getAddress( int $chain_ID, string $symbol ): ?string {
		foreach ( $this->token_data as $data ) {
			if ( $data[ self::CHAIN_ID_INDEX ] === $chain_ID && $data[ self::SYMBOL_INDEX ] === $symbol ) {
				return $data[ self::ADDRESS_INDEX ];
			}
		}
		return null;
	}

	/**
	 * トークンの小数点以下桁数を取得します。
	 */
	public function getDecimals( int $chain_ID, string $symbol ): ?int {
		foreach ( $this->token_data as $data ) {
			if ( $data[ self::CHAIN_ID_INDEX ] === $chain_ID && $data[ self::SYMBOL_INDEX ] === $symbol ) {
				return $data[ self::DECIMALS_INDEX ];
			}
		}
		return null;
	}

	/**
	 * 指定したチェーンIDに含まれる全てのトークンのシンボルを取得します。
	 *
	 * @param int $chain_ID
	 * @return string[]
	 */
	public function getAllSymbols( int $chain_ID ): array {
		$symbols = array();
		foreach ( $this->token_data as $data ) {
			if ( $data[ self::CHAIN_ID_INDEX ] === $chain_ID ) {
				$symbols[] = $data[ self::SYMBOL_INDEX ];
			}
		}
		return $symbols;
	}
}
