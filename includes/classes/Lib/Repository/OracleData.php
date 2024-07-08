<?php
declare(strict_types=1);

namespace Cornix\Serendipity\Core\Lib\Repository;

use Cornix\Serendipity\Core\Lib\Enum\ChainID;
use Cornix\Serendipity\Core\Lib\Enum\NetworkType;

class OracleData {

	private const CHAIN_ID_INDEX = 0;
	private const SYMBOL_INDEX   = 1;
	private const ADDRESS_INDEX  = 2;

	public function __construct() {
		$mainnet_chain_ID = ChainID::ETH_MAINNET;
		$oracle_data_json = <<<JSON
			{
				"data": [
					[ {$mainnet_chain_ID}, "AUD", "0x77F9710E7d0A19669A13c055F62cd80d313dF022" ],
					[ {$mainnet_chain_ID}, "ETH", "0x5f4eC3Df9cbd43714FE2740f5E3616155c5b8419" ],
					[ {$mainnet_chain_ID}, "EUR", "0xb49f677943BC038e9857d61E7d053CaA2C1734C1" ],
					[ {$mainnet_chain_ID}, "GBP", "0x5c0Ab2d9b5a7ed9f470386e82BB36A3613cDd4b5" ],
					[ {$mainnet_chain_ID}, "JPY", "0xBcE206caE7f0ec07b545EddE332A47C2F75bbeb3" ]
				]
			}
		JSON;

		$this->oracle_data = json_decode( $oracle_data_json, true )['data'];
	}
	private array $oracle_data;


	/**
	 * 指定したチェーン、シンボルに対するOracleのコントラクトアドレスを取得します。
	 */
	public function getAddress( int $chain_ID, string $symbol ): ?string {
		foreach ( $this->oracle_data as $data ) {
			if ( $data[ self::CHAIN_ID_INDEX ] === $chain_ID && $data[ self::SYMBOL_INDEX ] === $symbol ) {
				return $data[ self::ADDRESS_INDEX ];
			}
		}
		return null;
	}

	/**
	 * 指定したネットワーク種別に存在するOracleのシンボル(`XXX/USD`の`XXX`部分)一覧を取得します。
	 *
	 * @return string[]
	 */
	public function getSymbols( string $network_type ): array {
		$chain_ID = $this->getChainID( $network_type );
		/** @var string[] */
		$symbols = array();
		foreach ( $this->oracle_data as $data ) {
			if ( $data[ self::CHAIN_ID_INDEX ] === $chain_ID ) {
				$symbols[] = $data[ self::SYMBOL_INDEX ];
			}
		}
		return $symbols;
	}

	/**
	 * 指定されたネットワーク種別における、OracleのチェーンIDを取得します。
	 */
	private function getChainID( string $network_type ): int {
		switch ( $network_type ) {
			case NetworkType::MAINNET:
				return ChainID::ETH_MAINNET;
			case NetworkType::TESTNET:
				return ChainID::SEPOLIA;
			case NetworkType::PRIVATENET:
				return ChainID::HARDHAT;
			default:
				throw new \InvalidArgumentException( '[4EFECEE5] Invalid network type. - network_type: ' . $network_type );
		}
	}
}
